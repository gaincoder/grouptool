<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 22.08.2018
 * Time: 21:03
 */

namespace AppBundle\BotController;


use AppBundle\Entity\EventVote;
use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Router;

class AnswerEvent implements BotControllerInterface
{

    private $answerBot;
    private $entityManager;
    private $user;
    private $data;
    private $router;

    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager,Router $router,$data, UserInterface $user)
    {
        $this->answerBot = $answerBot;
        $this->entityManager = $entityManager;
        $this->data = $data;
        $this->user = $user;
        $this->router = $router;
    }

    public function execute()
    {
        $eventRepo = $this->entityManager->getRepository('AppBundle:Event');
        $event = $eventRepo->find($this->data->eventId);
        $voteRepo = $this->entityManager->getRepository('AppBundle:EventVote');
        if(!($answer = $voteRepo->getForEventAndUser($event,$this->user))){
            $answer = new EventVote();
            $answer->user = $this->user;
            $answer->event = $event;
        }
        $answer->vote = $this->data->answer;
        $this->entityManager->persist($answer);
        $this->entityManager->flush();
        $this->answerBot->getBot()->answerCallbackQuery($this->data->callbackId,'Antwort wurde gespeichert!');

        $answerText = $this->voteToText($answer->vote);

        if($event->owner instanceof UserInterface && $event->owner->telegramSupported()){
            $telegramBot = $this->answerBot;
            $url = $this->router->generate('event_view', ['event'=>$event->id], Router::ABSOLUTE_URL);
            $message = $this->user->getUsername()." hat bei deiner Veranstaltung ";
            $message .= '<a href=\'' . $url . '\'>'.$event->name . '</a>';
            $message .= ' seine/ihre Teilnahmeinformationen auf "'.$answerText.'" geändert.';
            $oldChatId = $telegramBot->chatId;
            $telegramBot->chatId = $event->owner->telegramChatId;
            $telegramBot->sendMessage($message);
            $telegramBot->chatId = $oldChatId;
        }
    }

    protected function voteToText($vote)
    {
        switch ((int)$vote) {
            case 1:
                return "dabei";
            case 2:
                return "nein";
            case 3:
                return "spontan";

        }
    }
}