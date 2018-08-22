<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 05.08.2017
 * Time: 06:22
 */

namespace AppBundle\Services;


use AppBundle\BotCommands\CommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Router;
use TelegramBot\Api\Types\ReplyKeyboardHide;

class TelegramReceiver
{
    private $answerBot;
    private $entityManager;
    private $router;
    private $photoUpload;

    public function __construct(TelegramBot $answerBot,EntityManagerInterface $entityManager,Router $router,PhotoUpload $photoUpload)
    {
        $this->answerBot = $answerBot;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->photoUpload = $photoUpload;
    }

    public function processMessage($message)
    {
        $dir = dirname(__FILE__).'/../../../var/logs';
        file_put_contents($dir.'/input.log',$message."\n",FILE_APPEND);
        $message = json_decode($message);
        if($this->isText($message)){
            $message->message->text = str_replace('@lg_portal_bot','',$message->message->text);
        }
        if($this->updateChatId($message)) {
            if ($this->photoIsActive() && $this->isPhoto($message)) {
                $this->photoUpload->upload($message);
            }
            if ($this->photoIsActive() && $this->isVideo($message)) {
                $this->photoUpload->uploadVideo($message);
            }
            if ($keyword = $this->getKeyWord($message)) {
                if ($command = $this->createCommand($keyword, $message)) {
                    if ($this->checkTime($command)) {
                        $command->execute($message);
                    }
                }
            } else {
                if ($this->isText($message) && trim(strtolower($message->message->text)) == 'moin') {
                    if ($command = $this->createCommand('Moin', $message)) {
                        $command->execute($message);
                    }
                }
            }
            if (isset($message->update_id)) {
                return $message->update_id;
            }
        }
    }

    private function updateChatId($message)
    {
        if(isset($message->message->chat) && $message->message->chat->type == 'private'){
            $this->answerBot->chatId = $message->message->chat->id;
            $userRepo = $this->entityManager->getRepository('AppBundle:User');
            $user = $userRepo->findOneBy(['telegramUsername'=>$message->message->chat->username]);
            if($user instanceof UserInterface)
            {
                $newuser = false;

                if($user->isEnabled()){
                    if(strlen((string)$user->telegramChatId) === 0){
                        $newuser = true;
                    }
                    $user->telegramChatId = $message->message->chat->id;
                }else{
                    $user->telegramChatId = null;
                }
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                if($newuser){
                    $this->answerBot->sendMessage('Hallo '.$user->getUsername().' :wink:! Die Einrichtung hat geklappt. Du kannst jetzt von mir Nachrichten empfangen.');
                }
                return true;
            }
            $this->answerBot->sendMessage('Zugriff verweigert! Unter dem Namen "'.$message->message->chat->username.'" konnte kein User gefunden werden. Bitte ggf. unter Einstellungen im Portal den Telegram-Nickname hinterlegen.');
            return false;
        }
        return true;
    }

    private function checkTime($command){
        $reflect = new \ReflectionClass($command);
        $dir = dirname(__FILE__).'/../../../var/logs';
        $file = $dir.DIRECTORY_SEPARATOR.$reflect->getShortName();
        if($reflect->getShortName() == 'Fotos'){
            return true;
        }
        if(file_exists($file) && filemtime($file) > (time()-60)){
            return false;
        }
        touch($file);
        return true;
    }

    private function getKeyWord($message){
        if($this->isText($message)){
            if($this->isKeyWord($message)){
                return $this->parseKeyWord($message);
            }
        }else{
            if($this->isCallback($message)){
                $this->removeKeyBoard($message->callback_query->message);
                if($message->callback_query->data == 'no'){
                    $this->answerBot->sendMessage('Vorgang abgebrochen!');
                }
                if($message->callback_query->data == 'yes'){
                    $newMessage = clone $message->callback_query->message->reply_to_message;
                    $newMessage->text = str_replace('/fotos','/createalbum',$newMessage->text);
                    $newMessage->text = str_replace('/Fotos','/createalbum',$newMessage->text);
                    $newUpdate = new \stdClass();
                    $newUpdate->message = $newMessage;
                    $this->processMessage(json_encode($newUpdate));
                    $newUpdate->message = $message->callback_query->message->reply_to_message;
                    $this->processMessage(json_encode($newUpdate));
                }
                $data = json_decode($message->callback_query->data);
                if(isset($data->action)){
                    $userRepo = $this->entityManager->getRepository('AppBundle:User');
                    $user = $userRepo->findOneBy(['telegramUsername'=>$message->callback_query->from->id]);
                    if($user instanceof UserInterface)
                    {
                        $data->data->callbackId = $message->callback_query->id;
                        $this->routeToBotController($data,$user);
                    }else{
                        $this->answerBot->getBot()->answerCallbackQuery($message->callback_query->id,"Benutzer nicht gefunden!\n\n Bitte Telegram Benutzernamen im Portal speichern und dem Bot eine private Nachricht schreiben.",true);
                    }
                }
            }
            return false;
        }
        return false;
    }

    private function isText($message)
    {
        return isset($message->message) && isset($message->message->text) && strlen
            ($message->message->text) > 0;
    }

    private function isCallback($message)
    {
        return isset($message->callback_query);
    }

    private function isKeyWord($message)
    {
        return strpos($message->message->text,'/') === 0;
    }

    private function parseKeyWord($message)
    {
        $word = str_replace('/', '', $message->message->text);
        $word = str_replace('@', ' ', $word);
        $wordArray = explode(' ',$word);
        $keyWord = ucfirst(strtolower($wordArray[0]));
        return $keyWord;
    }

    private function createCommand($keyword,$message)
    {
        $ns = 'AppBundle\\BotCommands\\';
        $class = $ns.$keyword;
        if(class_exists($class)){
            $bot = new TelegramBot($this->answerBot->getBotId(), $message->message->chat->id);
            $obj = new $class($bot,$this->entityManager,$this->router);
            return $obj;
        }
        return false;
    }

    private function routeToBotController($data,$user,$chatId)
    {
        $ns = 'AppBundle\\BotController\\';
        $class = $ns.ucfirst($data->action);
        if(class_exists($class)){
            $bot = new TelegramBot($this->answerBot->getBotId(), $chatId);
            $obj = new $class($bot,$this->entityManager,$this->router,$data->data,$user);
            $obj->execute();
            return $obj;
        }
        return false;
    }


    private function removeKeyboard($message){
        $this->answerBot->getBot()->editMessageReplyMarkup($message->chat->id,$message->message_id,null);
    }

    private function photoIsActive(){
        $dir = dirname(__FILE__).'/../../../var/logs/active_album/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        return iterator_count($fi) > 0;
    }
    private function isPhoto($message)
    {
        return isset($message->message) && isset($message->message->photo);
    }
    private function isVideo($message)
    {
        return isset($message->message) && isset($message->message->video);
    }

}