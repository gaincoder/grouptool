<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 22.08.2018
 * Time: 20:59
 */

namespace AppBundle\BotController;


use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Router;

interface BotControllerInterface
{



    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager,Router $router, $data,UserInterface $user);

    public function execute();
}