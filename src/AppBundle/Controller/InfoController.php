<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Info;
use AppBundle\Entity\News;
use AppBundle\Form\InfoFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class InfoController extends Controller
{
    /**
     * @Route("/info", name="info")
     */
    public function indexAction(Request $request)
    {
        if($this->isGranted('ROLE_STAMMI')) {
            $infos = $this->getDoctrine()->getRepository('AppBundle:Info')->findAllOrdered(1);
        }else{
            $infos = $this->getDoctrine()->getRepository('AppBundle:Info')->findAllOrdered(0);
        }
        return $this->render('info/index.html.twig', ['infos' => $infos]);
    }

    /**
     * @Route("/info/create", name="info_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        setlocale(LC_TIME, "de_DE");
        $info = new Info();
        $form = $this->createForm(InfoFormType::class, $info);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $info->createdBy = $this->getUser()->getUsername();
            $info->updatedBy = $this->getUser()->getUsername();
            $em = $this->getDoctrine()->getManager();
            $em->persist($info);
            $em->flush();
            $router = $this->get('router');
            $url = $router->generate('info', [], Router::ABSOLUTE_URL);
            $news = new News('Neue Info erstellt', ucfirst($this->getUser()->getUsername()).' hat "'.'<a href=\'' . $url . '#'.$info->id.'\'>'.$info->headline
                .'</a>" erstellt',$info->permission);
            $em->persist($news);
            $em->flush();

            if($info->permission == 0) {
                $telegramBot = $this->get('app.telegram.bot');

                $message = ":info: <b>Neue Info von ".$this->getUser()->getUsername()." hinzugefügt:</b> \n\n";
                $message .= '<a href=\'' . $url . '#'.$info->id.'\'>'.$info->headline . "</a> \n";
                $telegramBot->sendMessage($message);
            }
            
            $this->addFlash('success', 'Info wurde gespeichert!');
            return $this->redirectToRoute('info');
        }
        return $this->render('info/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Info hinzufügen']);
    }


    /**
     * @Route("/info/edit/{info}", name="info_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Info $info, Request $request)
    {
        $form = $this->createForm(InfoFormType::class, $info);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $info->updatedBy = $this->getUser()->getUsername();
            $em = $this->getDoctrine()->getManager();
            $em->persist($info);
            $em->flush();
            $this->addFlash('success', 'Info wurde gespeichert!');
            return $this->redirectToRoute('info');
        }
        return $this->render('info/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Info bearbeiten']);
    }

    /**
     * @Route("/info/delete/{info}/{confirm}", name="info_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Info $info, $confirm=false,Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Info']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($info);
        $em->flush();
        $this->addFlash('success', 'Info wurde gelöscht!');
        return $this->redirectToRoute('info');

    }

    /**
     * @Route("/info/share/{info}", name="info_share")
     * @param info $info
     * @param Request $request
     */
    public function share(info $info, Request $request)
    {
        if($info->permission == 0) {
            $telegramBot = $this->get('app.telegram.bot');
            $url = $this->get('router')->generate('info', [], Router::ABSOLUTE_URL);
            $message = ":info: <b>".$this->getUser()->getUsername()." möchte auf folgende Info hinweisen:</b> \n\n";
            $message .= "<b>".$info->headline."</b>\n\n";
            $message .= $info->text."\n\n";
            $message .= '<a href=\'' . $url . '\'>Infos anzeigen</a>';
            $telegramBot->sendMessage($message);
            $this->addFlash('success', 'Info wurde geteilt!');
        }else {
            $this->addFlash('danger', 'Teilen nicht möglich! Sichtbarkeit ist eingeschränkt!');
        }
        return $this->redirectToRoute('info');
    }
}
