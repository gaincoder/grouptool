<?php
/**
 * Copyright CDS Service GmbH. All rights reserved.
 * Creator: tim
 * Date: 08/08/17
 * Time: 16:43
 */


namespace AppBundle\BotCommands;

use AppBundle\Entity\Photoalbum;
use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Routing\Router;

/**
 * Class Hallo
 * @package AppBundle\BotCommands
 */
class Createalbum implements CommandInterface
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
        $title = trim(str_replace('/createalbum','',$message->message->text));

        if(!$album = $this->entityManager->getRepository('AppBundle:Photoalbum')->findOneBy(['name'=>$title])){
            $album = new Photoalbum();
            $album->name = $title;
            $this->entityManager->persist($album);
            $this->entityManager->flush();
            $answer = 'Das Album "'.$title.'" wurde angelegt.';
            $this->answerBot->sendMessage($answer);
        }
    }
}