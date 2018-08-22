<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 22.08.2018
 * Time: 21:03
 */

namespace AppBundle\BotController;


use AppBundle\Services\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Router;

class Yooo implements BotControllerInterface
{

    private $answerBot;
    private $entityManager;
    private $user;
    private $data;
    private $router;

    public function __construct(TelegramBot $answerBot, EntityManagerInterface $entityManager,Router $router,$data, UserInterface $user)
    {
        $this->answerBot = $answerBot;
        $this->entityManager = $entityManager;
        $this->data = $data;
        $this->user = $user;
        $this->router = $router;
    }

    public function execute()
    {
        $this->answerBot->getBot()->answerCallbackQuery($this->data->callbackId,"Benutzer ".$this->user->getUsername()." nicht gefunden!\n\n Bitte Telegram Benutzernamen im Portal speichern und dem Bot eine private Nachricht schreiben.",true);

    }
}