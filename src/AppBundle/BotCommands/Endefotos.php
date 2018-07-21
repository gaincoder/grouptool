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
class Endefotos implements CommandInterface
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
        $dir = dirname(__FILE__).'/../../../var/logs/active_album/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach($fi as $file){
            $album = $this->entityManager->getRepository('AppBundle:Photoalbum')->find(basename($file));
            unlink($file);
            $this->answerBot->sendMessage('Das Album "'.$album->name.'" wurde geschlossen! Wenn weitere Fotos hinzugefügt werden sollen, bitte erneut mit "/fotos '.$album->name.'" öffnen.');

        }
    }
}