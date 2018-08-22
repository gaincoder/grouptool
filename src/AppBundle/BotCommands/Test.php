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
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class Hallo
 * @package AppBundle\BotCommands
 */
class Test implements CommandInterface
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
        $jsonData = new \stdClass();
        $jsonData->action = "yooo";
        $button = ["text"=>'foo','callback_data'=>'baaaar'];
        $button2 = ["text"=>'foo2','callback_data'=>json_encode($jsonData)];
        $button3 = ["text"=>'foo3','callback_data'=>'nummer drei'];
        $inlineKeyBoard = new InlineKeyboardMarkup([[$button,$button2],[$button3]]);
        $this->answerBot->sendMessage("dies ist ein test",null,$inlineKeyBoard);
    }


}