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
        $button = ["text"=>'foo','callback_data'=>'baaaar'];
        $inlineKeyBoard = new InlineKeyboardMarkup([[$button]]);
        $this->answerBot->sendMessage("dies ist ein test",null,$inlineKeyBoard);
    }


}