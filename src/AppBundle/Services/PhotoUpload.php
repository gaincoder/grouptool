<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 06.09.2017
 * Time: 20:52
 */

namespace AppBundle\Services;


use AppBundle\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;

class PhotoUpload
{
    private $dir;
    private $entityManager;
    private $bot;


    public function __construct(TelegramBot $bot, EntityManagerInterface $entityManager)
    {
        $this->bot = $bot;
        $this->entityManager = $entityManager;
        $this->dir = $dir = dirname(__FILE__).'/../../../var/';

    }

    public function upload($message){
        $data = $message->message->photo;
        $album = $this->getAlbum();
        $id = uniqid();
        $filename = $id.'.jpg';
        $this->saveImage($data[count($data)-1]->file_id,$filename);
        $filenameThumb = $id.'_thumb.jpg';
        $this->saveImage($data[1]->file_id,$filenameThumb);
        $photo = new Photo();
        $photo->album = $album;
        $photo->photo = $filename;
        $photo->thumbnail = $filenameThumb;
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }



    public function uploadVideo($message){
        $data = $message->message->video;
        $album = $this->getAlbum();
        $id = uniqid();
        $filename = $id.'.mpg';
        $this->saveImage($data->file_id,$filename);
        $filenameThumb = $id.'_thumb.jpg';
        $this->saveImage($data->thumb->file_id,$filenameThumb);
        $photo = new Photo();
        $photo->album = $album;
        $photo->photo = $filename;
        $photo->thumbnail = $filenameThumb;
        $photo->type = 1;
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }


    /**
     * @return \AppBundle\Entity\Photoalbum
     */
    private function getAlbum(){
        $dir = $this->dir.'logs/active_album/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach($fi as $file){
            $album = $this->entityManager->getRepository('AppBundle:Photoalbum')->find(basename($file));
            return $album;
        }
    }

    private function saveImage($id,$filename){
        $album = $this->getAlbum();
        $dir = $this->dir.'photos/'.$album->id.'/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $data = $this->bot->getBot()->downloadFile($id);
        file_put_contents($dir.$filename,$data);
    }


}