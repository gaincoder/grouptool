<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Info;
use AppBundle\Entity\News;
use AppBundle\Form\InfoFormType;
use AppBundle\Form\SettingsFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    /**
     * @Route("/settings", name="settings")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(SettingsFormType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->telegramUsername = str_replace('@','',$user->telegramUsername );
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Einstellungen wurden gespeichert!');
        }
        return $this->render('settings/index.html.twig',['form'=>$form->createView()]);
    }


}
