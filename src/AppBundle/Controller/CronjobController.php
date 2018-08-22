<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 05.08.2017
 * Time: 06:30
 */

namespace AppBundle\Controller;


use AppBundle\Entity\News;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;


/**
 * Class CronjobController
 * @package AppBundle\Controller
 * @Route("/cronjob")
 */
class CronjobController extends Controller
{
    /**
     * @Route("/birthdayToday")
     */
    public function birthdayTodayAction(Request $request)
    {
        $birthdays = $this->getDoctrine()->getRepository('AppBundle:Birthday')->findToday();
        if(count($birthdays) > 0){
            $telegramBot = $this->get('app.telegram.bot');
            $message = ":balloon::balloon::balloon::balloon::balloon:\n\n";
            $em = $this->getDoctrine()->getManager();
            foreach ($birthdays as $birthday){
                $message.= '<b>'.$birthday->name.'</b> wird heute '.$birthday->getAgeThisYear()." Jahre alt!\n";
                $news = new News('Geburtstag',$birthday->name.' wird heute '.$birthday->getAgeThisYear()." Jahre alt!");
                $em->persist($news);
            }
            $message.= "Herzlichen Glückwunsch!\n\n";
            $message.= ":balloon::balloon::balloon::balloon::balloon:";
            $telegramBot->sendMessage($message);
            $em->flush();


        }



        return new JsonResponse(['success'=>true]);
    }

    /**
     * @Route("/birthdayPrewarning")
     */
    public function birthdayPrewarningAction(Request $request)
    {
        $birthdays = $this->getDoctrine()->getRepository('AppBundle:Birthday')->findInTwoWeeks();
        if(count($birthdays) > 0) {
            $telegramBot = $this->get('app.telegram.bot');
            $message = ":info::calendar: <b>Ankündigung Geburtstag:</b>\n\n";
            $em = $this->getDoctrine()->getManager();
            foreach ($birthdays as $birthday) {
                $message .= 'In 2 Wochen (' . $birthday->birthdate->format('d.m.') . ') wird ' . $birthday->name . ' ' . $birthday->getNextAge() . " Jahre alt.\n";
                $news = new News('Ankündigung Geburtstag', 'In 2 Wochen (' . $birthday->birthdate->format('d.m.') . ') wird ' . $birthday->name . ' ' . $birthday->getNextAge() . " Jahre alt.");
                $em->persist($news);
            }
            $telegramBot->sendMessage($message);
        }
        return new JsonResponse(['success'=>true]);
    }

    /**
     * @Route("/eventReminder/{execute}",defaults={"execute"=false}))
     */
    public function eventReminderAction($execute = false,Request $request)
    {
        if(date('w' ) == 0 || $execute){
            $events = $this->getDoctrine()->getRepository('AppBundle:Event')->findNextThree();
            $telegramBot = $this->get('app.telegram.bot');
            $message = ":info::calendar: <b>Kommende Veranstaltungen:</b>\n\n";
            $router = $this->get('router');

            foreach ($events as $event){
                $url = $router->generate('event_view',['event'=>$event->id],Router::ABSOLUTE_URL);
                $message.= $event->date->format('d.m.').' ';
                $icon = $event->public ? ':earth:' : ':lock:';
                $message.= $icon.' <a href=\''.$url.'\'>'.$event->name."</a>\n";
            }
            $telegramBot->sendMessage($message);
        }
        return new JsonResponse(['success'=>true]);
    }

    /**
     * @Route("/getChats")
     */
    public function getChatsAction(Request $request)
    {

        $telegramBot = $this->get('app.telegram.bot');
        $chats = $telegramBot->getChats();
        return new JsonResponse(['success'=>true,'chats'=>$chats]);
    }

    /**
     * @Route("/restartBot")
     */
    public function restartBot()
    {
        $dir = __DIR__.'/../../../';
        $fileTime = filemtime($dir.'/var/logs/telegramJob.log')."<br>";
        $restart = false;
        if($fileTime+60 < time()){
            $restart = true;
            $command = 'php5.6.30 '.$dir.'bin/console app:telegram-read ';
            exec($command);
        }
        return new JsonResponse(['success'=>true,'restart'=>$restart]);
    }

    /**
     * @Route("/closeAlbum")
     */
    public function closeAlbum()
    {
        $dir = dirname(__FILE__).'/../../../var/logs/active_album/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
            return new JsonResponse(['success'=>true]);
        }
        $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $telegramBot = $this->get('app.telegram.bot');
        if(iterator_count($fi) > 0){
            foreach($fi as $file){
                if((filemtime($file)+299) < time()) {
                    $album = $this->getDoctrine()->getRepository('AppBundle:Photoalbum')->find(basename($file));
                    unlink($file);
                    $telegramBot->sendMessage('Die 5 Minuten sind um! Das Album "'.$album->name.'" wurde geschlossen! Wenn weitere Fotos hinzugefügt werden sollen, bitte erneut mit "/fotos '.$album->name.'" öffnen.');
                }
            }
        }
        return new JsonResponse(['success'=>true]);
    }
}