<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Birthday;
use AppBundle\Entity\User;
use AppBundle\Form\BirthdayFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdministrationController extends Controller
{
    /**
     * @Route("/administration", name="administration")
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException();
        }
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        return $this->render('administration/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/administration/toggleUse/{user}", name="administration_toggle_user")
     */
    public function toggleUser(User $user,Request $request)
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException();
        }

        $user->setEnabled(!$user->isEnabled());
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('administration');
    }


    /**
     * @Route("/administration/toggleStammi/{user}", name="administration_toggle_stammi")
     */
    public function toggleStammi(User $user,Request $request)
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException();
        }

        if($user->hasRole('ROLE_STAMMI')){
            $user->removeRole('ROLE_STAMMI');
        }else{
            $user->addRole('ROLE_STAMMI');
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('administration');
    }
}
