<?php
/**
 * Copyright CDS Service GmbH. All rights reserved.
 * Creator: tim
 * Date: 08/08/17
 * Time: 16:43
 */


namespace AppBundle\BotCommands;

use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Router;

/**
 * Class Hallo
 * @package AppBundle\BotCommands
 */
class Veranstaltungen implements CommandInterface
{

    private $answerBot;
    private $entityManager;
    private $router;

    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager,Router $router)
    {
        $this->answerBot = $answerBot;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function execute($message)
    {
        $events = $this->entityManager->getRepository('AppBundle:Event')->findNextThree();
        $telegramBot = $this->answerBot;
        $message = ":info::calendar: <b>Kommende Veranstaltungen:</b>\n\n";
        $router = $this->router;

        foreach ($events as $event) {
            $url = $router->generate('event_view', ['event' => $event->id], Router::ABSOLUTE_URL);
            $message .= $event->date->format('d.m.').' ';
            $message .= '<a href=\''.$url.'\'>'.$event->name."</a>\n";
        }
        $telegramBot->sendMessage($message);
    }
}