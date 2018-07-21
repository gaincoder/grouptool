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
class Geburtstage implements CommandInterface
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
        $birthdays = $this->entityManager->getRepository('AppBundle:Birthday')->findAllOrderedByDay();
        $telegramBot = $this->answerBot;
        $message = ":info::balloon: <b>Geburtstage:</b>\n\n";

        foreach ($birthdays as $birthday) {
            $message.= $birthday->birthdate->format('d.m.y')." ".$birthday->name."\n";
        }
        $telegramBot->sendMessage($message);
    }
}