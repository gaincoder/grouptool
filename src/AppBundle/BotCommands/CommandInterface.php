<?php
/**
 * Copyright CDS Service GmbH. All rights reserved.
 * Creator: tim
 * Date: 08/08/17
 * Time: 16:39
 */


namespace AppBundle\BotCommands;

use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Router;

/**
 * Class CommandInterface
 * @package AppBundle\BotCommands
 */
interface CommandInterface
{
    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager, Router $router);

    public function execute($message);
}