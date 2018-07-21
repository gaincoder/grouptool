<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Poll;
use AppBundle\Entity\News;
use AppBundle\Entity\UserVote;
use AppBundle\Form\CommentFormType;
use AppBundle\Form\PollFormType;
use AppBundle\Form\PollSimpleFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class PollController extends Controller
{
    /**
     * @Route("/poll", name="poll")
     */
    public function indexAction(Request $request)
    {
        if($this->isGranted('ROLE_STAMMI')) {
            $polls = $this->getDoctrine()->getRepository('AppBundle:Poll')->findAllOrdered(1);
        }else{
            $polls = $this->getDoctrine()->getRepository('AppBundle:Poll')->findAllOrdered(0);
        }
        return $this->render('poll/index.html.twig', ['polls' => $polls]);
    }

    /**
     * @Route("/poll/create", name="poll_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $poll = new Poll();
        $form = $this->createForm(PollFormType::class, $poll);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $poll->createdBy = $this->getUser()->getUsername();
            $poll->updatedBy = $this->getUser()->getUsername();
            $em = $this->getDoctrine()->getManager();
            $poll->owner = $this->getUser();
            $em->persist($poll);
            $router = $this->get('router');
            $url = $router->generate('poll_view', ['poll'=>$poll->id], Router::ABSOLUTE_URL);
            $news = new News('Neue Umfrage erstellt','"<a href="' . $url . '">'.$poll->name.'</a>" wurde 
            von '.$this->getUser()->getUsername().' erstellt',$poll->permission);
            $em->persist($news);
            $em->flush();

            if($poll->permission == 0) {
                $telegramBot = $this->get('app.telegram.bot');

                $message = ":info: <b>Neue Umfrage von ".$this->getUser()->getUsername()." hinzugefügt:</b> \n\n";
                $message .= '<a href=\'' . $url . '\'>'.$poll->name . '</a>';
                $telegramBot->sendMessage($message);
            }

            $this->addFlash('success', 'Umfrage wurde gespeichert!');
            return $this->redirectToRoute('poll_view',['poll'=>$poll->id]);
        }
        return $this->render('poll/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Umfrage hinzufügen']);
    }


    /**
     * @Route("/poll/edit/{poll}", name="poll_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Poll $poll, Request $request)
    {
        if($poll->owner == $this->getUser() || $this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(PollFormType::class, $poll);
            $tpl = 'poll/form.html.twig';
        }else{
            $form = $this->createForm(PollSimpleFormType::class, $poll);
            $tpl = 'poll/form_simple.html.twig';
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $poll->updatedBy = $this->getUser()->getUsername();
            $em = $this->getDoctrine()->getManager();
            $em->persist($poll);
            $em->flush();
            $this->addFlash('success', 'Umfrage wurde gespeichert!');
            return $this->redirectToRoute('poll_view',['poll'=>$poll->id]);
        }
        return $this->render($tpl, ['form' => $form->createView(), 'page_title' => 'Umfrage bearbeiten','poll'=>$poll]);
    }

    /**
     * @Route("/poll/delete/{poll}/{confirm}", name="poll_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Poll $poll, $confirm=false,Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Umfrage']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($poll);
        $em->flush();
        $this->addFlash('success', 'Umfrage wurde gelöscht!');
        return $this->redirectToRoute('poll');

    }

    /**
     * @Route("/poll/view/{poll}", name="poll_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view(Poll $poll, Request $request)
    {

        $comment = new Comment();
        $commentform = $this->createForm(CommentFormType::class, $comment);
        $commentform->handleRequest($request);
        if ($commentform->isSubmitted() && $commentform->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $comment->user = $this->getUser();
            $em->persist($comment);
            $poll->comments[] = $comment;
            $em->persist($poll);
            $em->flush();
            if($poll->owner->telegramSupported()){
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('poll_view', ['poll'=>$poll->id], Router::ABSOLUTE_URL);
                $message = $this->getUser()->getUsername()." hat deine Umfrage ";
                $message .= '<a href=\'' . $url . '\'>'.$poll->name . '</a>';
                $message .= ' kommentiert.';
                $telegramBot->chatId = $poll->owner->telegramChatId;
                $telegramBot->sendMessage($message);
            }
            return $this->redirectToRoute('poll_view', ['poll' => $poll->id]);
        }

        $answerRepo = $this->getDoctrine()->getRepository('AppBundle:PollAnswer');
        $answers = $answerRepo->getOrderedForPoll($poll);

        return $this->render('poll/view.html.twig', ['poll'=>$poll,'commentform'=>$commentform->createView(),'answers'=>$answers]);
    }

    /**
     * @Route("/poll/save/{poll}", name="poll_save")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function save(Poll $poll, Request $request){
        if($poll->isOpen() && $request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $pollAnswerRepo = $em->getRepository('AppBundle:PollAnswer');
            foreach($poll->getAnswers() as $answer){
                $userVote = $answer->getVoteForUser($this->getUser());
                if($userVote instanceof UserVote) {
                    $em->remove($userVote);
                }
            }
            $em->flush();
            $answers = array();
            if(isset($_POST['answer']) && is_array($_POST['answer'])){
                $answers = $_POST['answer'];
            }
            foreach($answers as $answerId => $vote){
                $userVote = new UserVote();
                $userVote->user = $this->getUser();
                $userVote->vote = 1;
                $userVote->answer = $pollAnswerRepo->find($vote);
                $em->persist($userVote);
            }
            $em->flush();
            $this->addFlash('success','Antworten wurden gespeichert!');
            if($poll->owner->telegramSupported()){
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('poll_view', ['poll'=>$poll->id], Router::ABSOLUTE_URL);
                $message = "Deine Umfrage ";
                $message .= '<a href=\'' . $url . '\'>'.$poll->name . '</a>';
                $message .= ' wurde von '.$this->getUser()->getUsername().' beantwortet.';
                $telegramBot->chatId = $poll->owner->telegramChatId;
                $telegramBot->sendMessage($message);
            }
        }
        return $this->redirectToRoute('poll_view',['poll'=>$poll->id]);

    }


    /**
     * @Route("/poll/toggleStatus/{poll}", name="poll_toggle_status")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleStatus(Poll $poll, Request $request)
    {
        $poll->closed = !$poll->closed;
        $em = $this->getDoctrine()->getManager();
        $em->persist($poll);
        $em->flush();
        return $this->redirectToRoute('poll_view',['poll'=>$poll->id]);
    }

    /**
     * @Route("/poll/share/{poll}", name="poll_share")
     * @param Poll $poll
     * @param Request $request
     */
    public function share(Poll $poll, Request $request)
    {
        if($poll->permission == 0) {
            $telegramBot = $this->get('app.telegram.bot');
            $url = $this->get('router')->generate('poll_view', ['poll'=>$poll->id], Router::ABSOLUTE_URL);
            $message = ":info: <b>".$this->getUser()->getUsername()." möchte auf folgende Umfrage hinweisen:</b> \n\n";
            $message .= '<a href=\'' . $url . '\'>'.$poll->name . '</a>';
            $telegramBot->sendMessage($message);
            $this->addFlash('success', 'Umfrage wurde geteilt!');
        }else {
            $this->addFlash('danger', 'Teilen nicht möglich! Sichtbarkeit ist eingeschränkt!');
        }
        return $this->redirectToRoute('poll_view',['poll'=>$poll->id]);
    }
}
