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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Routing\Router;

/**
 * Class Hallo
 * @package AppBundle\BotCommands
 */
class Hilfe implements CommandInterface
{


    private $answerBot;
    private $entityManager;
    private $router;

    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager, Router $router)
    {
        $this->answerBot = $answerBot;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function execute($message)
    {
        $answer = "Folgende Befehle kenne ich:\n\n";
        $answer .= "/hilfe\nZeigt diese Liste an\n\n";
        $answer .= "/veranstaltungen\nZeigt die nächsten Veranstaltungen\n\n";
        $answer .= "/geburtstage\nGeburtstagsliste\n\n";
        $answer .= "/portal\nLink zum Gruppenportal\n\n";
        $answer .= "/fotos -Name des Albums-\nFotos hochladen";
        $this->answerBot->sendMessage($answer);
    }


}