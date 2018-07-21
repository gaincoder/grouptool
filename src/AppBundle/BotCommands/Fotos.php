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
class Fotos implements CommandInterface
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
        $title = trim(str_replace('/fotos','',$message->message->text));
        $title = trim(str_replace('/Fotos','',$title));
        $dir = dirname(__FILE__).'/../../../var/logs/active_album/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach($fi as $file){
            $album = $this->entityManager->getRepository('AppBundle:Photoalbum')->find(basename($file));
            $this->answerBot->getBot()->sendMessage(
                $this->answerBot->chatId,
                'Es ist bereits das Album "'.$album->name.'" geöffnet! Bitte warten oder /endefotos senden!',
                null,
                true,
                $message->message->message_id
            );

            return;
        }
        if(strlen($title) == 0){
            $this->answerBot->getBot()->sendMessage($this->answerBot->chatId,'Bitte hinter /fotos den Titel des Albums angeben',null,true,$message->message->message_id);
            return;
        }
        if($album = $this->entityManager->getRepository('AppBundle:Photoalbum')->findOneBy(['name'=>$title])){
            $answer = 'Fotos die in den nächsten 5 Minuten in die Gruppe geschickt werden, werden dem  Album "'.$title
                .'" hinzugefügt. Vorher kann das hochladen mit /endefotos beendet werden.';

            touch($dir.$album->id);
            $this->answerBot->sendMessage($answer);
        }else{
            $answer = 'Das Album "'.$title.'" wurde nicht gefunden. Soll es neu angelegt werden?';
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
                [
                    [
                        ['text' => 'Ja','callback_data'=>'yes'],
                        ['text' => 'Nein','callback_data'=>'no']
                    ]
                ],
                true,false,true
            );

            $this->answerBot->getBot()->sendMessage($this->answerBot->chatId,$answer,null,true,$message->message->message_id,$keyboard);
        }

    }
}