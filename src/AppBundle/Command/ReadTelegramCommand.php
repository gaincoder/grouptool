<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 11/08/17
 * Time: 16:18
 */

namespace AppBundle\Command;

use AppBundle\Services\TelegramReceiver;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadTelegramCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:telegram-read')
            ->setDescription('read telegram updates')
            ->addArgument('offset',null,InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reciever = $this->getContainer()->get('app.telegram.receiver');
        $bot = $this->getContainer()->get('app.telegram.bot');
        $url = 'https://api.telegram.org/bot' . $bot->getBotId() . '/getUpdates';


        $json = file_get_contents($url);
        $result = json_decode($json);

        $lastId = (int)$input->getArgument('offset');
        foreach ($result->result as $update){
            $lastId = $reciever->processMessage(json_encode($update))+1;
        }
    /*$dir = __DIR__.'/../../../';
        //$command = 'php5.6.30 '.$dir.'bin/console app:telegram-read ';
        //$command .= $lastId;
        //$command .= " &> /dev/null &";
        $msg = date('Y-m-d H:i:s');
        $msg.= ' '.$command."\n";
        exec($command);
        file_put_contents($dir.'/var/logs/telegramJob.log',$msg,FILE_APPEND);*/
    }
}