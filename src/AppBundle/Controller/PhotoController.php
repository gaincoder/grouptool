<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Photoalbum;
use AppBundle\Entity\News;
use AppBundle\Form\CommentFormType;
use AppBundle\Form\PhotoalbumFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /**
     * @Route("/photoalbums", name="photoalbum")
     */
    public function indexAction(Request $request)
    {

        $albums = $this->getDoctrine()->getRepository('AppBundle:Photoalbum')->findAllOrdered($this->isGranted('ROLE_STAMMI'));
        return $this->render('photoalbum/index.html.twig', ['photoalbums' => $albums]);
    }

    /**
     * @Route("/photoalbum/create", name="photoalbum_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        setlocale(LC_TIME, "de_DE");
        $photoalbum = new Photoalbum();
        $form = $this->createForm(PhotoalbumFormType::class, $photoalbum);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($photoalbum);
            $em->flush();
            $router = $this->get('router');
            $url = $router->generate('photoalbum_view',['photoalbum'=>$photoalbum->id],Router::ABSOLUTE_URL);

            $news = new News('Neues Fotoalbum erstellt', ucfirst($this->getUser()->getUsername()).' hat <a href="'
                .$url.'">'.$photoalbum->name.'</a> ',$photoalbum->permission);
            $em->persist($news);
            $em->flush();
            if($photoalbum->permission == 0) {
                $telegramBot = $this->get('app.telegram.bot');
                $router = $this->get('router');
                $url = $router->generate('photoalbum_view', ['photoalbum' => $photoalbum->id], Router::ABSOLUTE_URL);
                $message = ":info: <b>Neues Fotoalbum von ".$this->getUser()->getUsername()." hinzugefügt:</b> \n\n";
                $message .= '<a href=\'' . $url . '\'>' . $photoalbum->name . "</a>\n";
                $telegramBot->sendMessage($message);
            }
            $this->addFlash('success', 'Fotoalbum wurde gespeichert!');
            return $this->redirectToRoute('photoalbum');
        }
        return $this->render('photoalbum/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Fotoalbum hinzufügen']);
    }


    /**
     * @Route("/photoalbum/edit/{photoalbum}", name="photoalbum_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Photoalbum $photoalbum, Request $request)
    {
        $form = $this->createForm(PhotoalbumFormType::class, $photoalbum);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($photoalbum);
            $em->flush();
            $this->addFlash('success', 'Fotoalbum wurde gespeichert!');
            return $this->redirectToRoute('photoalbum');
        }
        return $this->render('photoalbum/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Fotoalbum bearbeiten']);
    }

    /**
     * @Route("/photoalbum/delete/{photoalbum}/{confirm}", name="photoalbum_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Photoalbum $photoalbum, $confirm=false, Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Fotoalbum']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($photoalbum);
        $em->flush();
        $this->addFlash('success', 'Fotoalbum wurde gelöscht!');
        return $this->redirectToRoute('photoalbum');

    }

    /**
 * @Route("/photoalbum/view/{photoalbum}", name="photoalbum_view")
 * @param Request $request
 * @return \Symfony\Component\HttpFoundation\Response
 */
    public function view(Photoalbum $photoalbum, Request $request)
    {
        return $this->render('photoalbum/view.html.twig', ['photoalbum'=>$photoalbum]);
    }


    /**
     * @Route("/photo/display/{photo}/{thumb}", name="photo_display",defaults={"thumb"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Photo $photo, $thumb=false, Request $oRequest)
    {
        $oResponse = new Response();
        $dir = dirname(__FILE__).'/../../../var/photos/';
        $dir .= $photo->album->id.'/';
        if($thumb){
            $file = $photo->thumbnail;
        }else{
            $file = $photo->photo;
        }
        $sFileName = $dir.$file;
        if( ! is_file($sFileName)){
            $oResponse->setStatusCode(404);

            return $oResponse;
        }

        // Caching...
        $sLastModified = filemtime($sFileName);
        $sEtag = md5_file($sFileName);

        $sFileSize = filesize($sFileName);
        $aInfo = getimagesize($sFileName);
        $type = $aInfo['mime'];
        if($photo->type == 1){
            $type = "video/mp4";
        }

        if(in_array($sEtag, $oRequest->getETags()) || $oRequest->headers->get('If-Modified-Since') === gmdate("D, d M Y H:i:s", $sLastModified)." GMT" ){
            $oResponse->headers->set("Content-Type", $type);
            $oResponse->headers->set("Last-Modified", gmdate("D, d M Y H:i:s", $sLastModified)." GMT");
            $oResponse->setETag($sEtag);
            $oResponse->setPublic();
            $oResponse->setStatusCode(304);

            return $oResponse;
        }

        $oStreamResponse = new StreamedResponse();
        $oStreamResponse->headers->set("Content-Type", $type);
        $oStreamResponse->headers->set("Content-Length", $sFileSize);
        $oStreamResponse->headers->set("ETag", $sEtag);
        $oStreamResponse->headers->set("Last-Modified", gmdate("D, d M Y H:i:s", $sLastModified)." GMT");

        $oStreamResponse->setCallback(function() use ($sFileName) {
            readfile($sFileName);
        });

        return $oStreamResponse;
    }
    /**
     * @Route("/photo/displayVideo/{photo}.mp4", name="photo_displayvideo")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayVideo(Photo $photo , Request $oRequest)
    {
        $oResponse = new Response();
        $dir = dirname(__FILE__).'/../../../var/photos/';
        $dir .= $photo->album->id.'/';

            $file = $photo->photo;

        $sFileName = $dir.$file;
        $size=filesize($sFileName);

        $fm=@fopen($sFileName,'rb');
        if(!$fm) {
            $oResponse->setStatusCode(404);

            return $oResponse;
        }

        $begin=0;
        $end=$size;

        if(isset($_SERVER['HTTP_RANGE'])) {
            if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin=intval($matches[1]);
                if(!empty($matches[2])) {
                    $end=intval($matches[2]);
                }
            }
        }



        // Caching...
        $sLastModified = filemtime($sFileName);
        $sEtag = md5_file($sFileName);

        $sFileSize = filesize($sFileName);
        $aInfo = getimagesize($sFileName);
        $type = $aInfo['mime'];
        if($photo->type == 1){
            $type = "video/mp4";
        }


        $oStreamResponse = new Response();

        if($begin>0||$end<$size)
            $oStreamResponse->setStatusCode(206);
        else
            $oStreamResponse->setStatusCode(200);
        $oStreamResponse->headers->set("Content-Type", $type);
        $oStreamResponse->headers->set("Accept-Ranges", "bytes");
        $oStreamResponse->headers->set("Content-Disposition", "inline");
        $oStreamResponse->headers->set("Content-Transfer-Encoding", "binary\n");
        $oStreamResponse->headers->set("Content-Length", ($end-$begin));
        $oStreamResponse->headers->set("Content-Range",
            "bytes $begin-$end/$size");
        $oStreamResponse->headers->set("ETag", $sEtag);
        $oStreamResponse->headers->set("Last-Modified", gmdate("D, d M Y H:i:s", $sLastModified)." GMT");

        $cur=$begin;
        fseek($fm,$begin,0);

        $content = "";
        while(!feof($fm)&&$cur<$end&&(connection_status()==0))
        { $content .= fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
            usleep(1000);
        }


        return $oStreamResponse->setContent($content);
    }

}
