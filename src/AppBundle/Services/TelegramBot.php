<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 05.08.2017
 * Time: 06:22
 */

namespace AppBundle\Services;


use TelegramBot\Api\BotApi;

class TelegramBot
{

    protected $bot;
    protected $botId;
    public $chatId;

    public function __construct($botId, $chatId)
    {
        $this->bot = new \TelegramBot\Api\BotApi($botId);
        $this->botId = $botId;
        $this->chatId = $chatId;
    }

    public function sendMessage($message, $replyToMessageId = null,
                                $replyMarkup = null)
    {
        $message = $this->parseEmoji($message);
        $this->bot->sendMessage($this->chatId, $message, 'HTML', true, $replyToMessageId, $replyMarkup);
    }

    /**
     * @return BotApi
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @return mixed
     */
    public function getBotId()
    {
        return $this->botId;
    }


    protected function parseEmoji($string)
    {
        $emojiMap = [
            'balloon' => "\xF0\x9F\x8E\x88",
            'calendar' => "\xF0\x9F\x93\x85",
            'info' => "\xE2\x84\xB9",
            'question' => "\xE2\x9D\x93",
            'wink' => "\xF0\x9F\x91\x8B",
            'earth' => "\xF0\x9F\x8C\x8F",
            'lock' => "\xF0\x9F\x94\x92",
        ];
        foreach ($emojiMap as $emojiName => $emojiCode) {
            $string = str_replace(':' . $emojiName . ':', $emojiCode, $string);
        }
        return $string;
    }

    public function getChats()
    {
        $updates = $this->bot->call('getUpdates');
        $chats = array();
        /*foreach($updates as $update){
            $chats[$update['message']['chat']['id']] = $update['message']['chat'];
        }*/
        return $updates;
    }

}