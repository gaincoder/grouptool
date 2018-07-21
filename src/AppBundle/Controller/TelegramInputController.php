<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Info;
use AppBundle\Entity\News;
use AppBundle\Form\InfoFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class InfoController
 * @package AppBundle\Controller
 * @Route("/telegramInput")
 */
class TelegramInputController extends Controller
{



    /**
     * @Route("/listen", name="telegram_listen")
     */
    public function indexAction(Request $request)
    {
        $reciever = $this->get('app.telegram.receiver');

        $input = file_get_contents('php://input');
        if(strlen($input) > 0){
            $reciever->processMessage($input);
        }
        return new JsonResponse(['success'=>true]);
    }


    /**
     * @Route("/getChats", name="telegram_getchats")
     */
    public function getChatsAction(Request $request)
    {
        $bot = $this->get('app.telegram.bot');

        $chats = $bot->getChats();

        return new JsonResponse(['success' => true,'chats'=>$chats]);
    }

    /**
     * @Route("/getFile/{id}", name="telegram_getfile")
     */
    public function getFileAction($id,Request $request)
    {
        header('Content-Type: image/jpeg');
        $bot = $this->get('app.telegram.bot');

        $chats = $bot->getBot()->downloadFile($id);

        die($chats);

        return new JsonResponse(['success' => true,'chats'=>$chats]);
    }

    /**
     * @Route("/enable", name="telegram_enable")
     */
    public function enableAction(Request $request)
    {
        $bot = $this->get('app.telegram.bot');

        $result = $bot->getBot()->call('setWebhook',['url'=>'https://lippe.grouptool.de/telegramInput/listen']);
        return new JsonResponse(['success' => true, 'result' => $result]);
    }


    /**
     * @Route("/disable", name="telegram_disable")
     */
    public function disableAction(Request $request)
    {
        $bot = $this->get('app.telegram.bot');

        $result = $bot->getBot()->call('deleteWebhook');
        return new JsonResponse(['success' => true, 'result' => $result]);
    }
}
