<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventVote;
use AppBundle\Entity\News;
use AppBundle\Form\CommentFormType;
use AppBundle\Form\EventFormType;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class EventController extends Controller
{
    /**
     * @Route("/event", name="event")
     */
    public function indexAction(Request $request)
    {

        $events = $this->getDoctrine()->getRepository('AppBundle:Event')->findFuture($this->isGranted('ROLE_STAMMI'));
        return $this->render('event/index.html.twig', ['events' => $events]);
    }

    /**
     * @Route("/event/create", name="event_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        setlocale(LC_TIME, "de_DE");
        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->createdBy = $this->getUser()->getUsername();
            $event->updatedBy = $this->getUser()->getUsername();
            $event->owner = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            $router = $this->get('router');
            $url = $router->generate('event_view',['event'=>$event->id],Router::ABSOLUTE_URL);

            $news = new News('Neue Veranstaltung erstellt', ucfirst($this->getUser()->getUsername()).' hat <a href="'
            .$url.'">'.$event->name.'</a> am '.$event->date->format('d.m.y')." erstellt",$event->permission);
            $em->persist($news);
            $em->flush();
            if($event->permission == 0) {
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('event_view', ['event' => $event->id], Router::ABSOLUTE_URL);
                $message = ":info: <b>Neue Veranstaltung von ".$this->getUser()->getUsername()." hinzugefügt:</b> \n\n";
                $message .= '<a href=\'' . $url . '\'>' . $event->name . "</a> am " . $event->date->format('d.m.y') . "\n";
                $telegramBot->sendMessage($message,null,$this->getKeyboard($event));
            }
            $this->addFlash('success', 'Veranstaltung wurde gespeichert!');
            return $this->redirectToRoute('event');
        }
        return $this->render('event/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Veranstaltung hinzufügen']);
    }


    /**
     * @Route("/event/edit/{event}", name="event_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Event $event, Request $request)
    {
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->updatedBy = $this->getUser()->getUsername();
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Veranstaltung wurde gespeichert!');
            return $this->redirectToRoute('event');
        }
        return $this->render('event/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Veranstaltung bearbeiten']);
    }

    /**
     * @Route("/event/delete/{event}/{confirm}", name="event_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Event $event, $confirm=false, Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Veranstaltung']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Veranstaltung wurde gelöscht!');
        return $this->redirectToRoute('event');

    }

    /**
     * @Route("/event/view/{event}", name="event_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view(Event $event, Request $request)
    {
        $comment = new Comment();
        $commentform = $this->createForm(CommentFormType::class, $comment);
        $commentform->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($commentform->isSubmitted() && $commentform->isValid()) {

            $comment->user = $this->getUser();
            $em->persist($comment);
            $event->comments[] = $comment;
            $em->persist($event);
            $em->flush();
            if($event->owner instanceof UserInterface && $event->owner->telegramSupported()){
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('event_view', ['event'=>$event->id], Router::ABSOLUTE_URL);
                $message = $this->getUser()->getUsername()." hat einen Kommmentar zu deiner Veranstaltung  ";
                $message .= '<a href=\'' . $url . '\'>'.$event->name . '</a>';
                $message .= ' dagelassen.';
                $telegramBot->chatId = $event->owner->telegramChatId;
                $telegramBot->sendMessage($message);
            }
            return $this->redirectToRoute('event_view', ['event' => $event->id]);
        }
        $voteRepo = $em->getRepository('AppBundle:EventVote');
        if(!($answer = $voteRepo->getForEventAndUser($event,$this->getUser()))){
           $current = 0;
        }else{
            $current = $answer->vote;
        }
        return $this->render('event/view.html.twig', ['event'=>$event,'current'=>$current,'commentform'=>$commentform->createView(),'voteRepo'=>$voteRepo]);
    }

    /**
     * @Route("/event/share/{event}", name="event_share")
     * @param event $event
     * @param Request $request
     */
    public function share(event $event, Request $request)
    {
        if($event->permission == 0) {
            $telegramBot = $this->get('app.telegram.bot');
            $url = $this->get('router')->generate('event_view', ['event'=>$event->id], Router::ABSOLUTE_URL);
            $message = ":info: <b>".$this->getUser()->getUsername()." möchte auf folgende Veranstaltung hinweisen:</b> \n\n";
            $message .= '<a href=\'' . $url . '\'>'.$event->name . '</a>';
            $telegramBot->sendMessage($message,null,$this->getKeyboard($event));
            $this->addFlash('success', 'Veranstaltung wurde geteilt!');
        }else {
            $this->addFlash('danger', 'Teilen nicht möglich! Sichtbarkeit ist eingeschränkt!');
        }
        return $this->redirectToRoute('event_view',['event'=>$event->id]);
    }

    protected function getKeyboard(event $event)
    {
        $answerString = 'AnswerEvent;'.$event->id.';';
        $yes = $answerString.'1';
        $yesBtn = ["text"=>'Dabei','callback_data'=>$yes];
        $no =  $answerString.'2';
        $noBtn =["text"=>'Nein','callback_data'=>$no];
        $impulse =  $answerString.'3';

        $btns = [[$yesBtn],[$noBtn]];

        if($event->disableImpulse != true){
            $impulseBtn = ["text"=>'Spontan','callback_data'=>$impulse];
            $btns[] = [$impulseBtn];
        }
        return new InlineKeyboardMarkup($btns);

    }


    /**
     * @Route("/event/save/{event}", name="event_save")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function save(Event $event, Request $request)
    {
        if($request->isMethod('POST') && isset($_POST['answer'])){
            $em = $this->getDoctrine()->getManager();
            $voteRepo = $em->getRepository('AppBundle:EventVote');
            if(!($answer = $voteRepo->getForEventAndUser($event,$this->getUser()))){
                $answer = new EventVote();
                $answer->user = $this->getUser();
                $answer->event = $event;
            }
            $answer->vote = $_POST['answer'];
            $em->persist($answer);
            $em->flush();
            $this->addFlash('success','Antwort wurde gespeichert!');
            if($event->owner instanceof UserInterface && $event->owner->telegramSupported()){
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('event_view', ['event'=>$event->id], Router::ABSOLUTE_URL);
                $message = $this->getUser()->getUsername()." hat bei deiner Veranstaltung ";
                $message .= '<a href=\'' . $url . '\'>'.$event->name . '</a>';
                $message .= ' seine Teilnahmeinformationen geändert.';
                $telegramBot->chatId = $event->owner->telegramChatId;
                $telegramBot->sendMessage($message);
            }
        }
        return $this->redirectToRoute('event_view',['event'=>$event->id]);
    }

}
