<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Birthday;
use AppBundle\Form\BirthdayFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BirthdayController extends Controller
{
    /**
     * @Route("/birthday", name="birthday")
     */
    public function indexAction(Request $request)
    {
        $pastBirthdays = $this->getDoctrine()->getRepository('AppBundle:Birthday')->findAllPastOrderedByDay();
        $futureBirthdays = $this->getDoctrine()->getRepository('AppBundle:Birthday')->findAllFutureOrderedByDay();
        return $this->render('birthday/index.html.twig', ['pastBirthdays' => $pastBirthdays, 'futureBirthdays' => $futureBirthdays]);
    }

    /**
     * @Route("/birthday/create", name="birthday_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        setlocale(LC_TIME, "de_DE");
        $birthday = new Birthday();
        $form = $this->createForm(BirthdayFormType::class, $birthday);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($birthday);
            $em->flush();
            $this->addFlash('success', 'Geburtstag wurde gespeichert!');
            return $this->redirectToRoute('birthday');
        }
        return $this->render('birthday/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Geburtstag hinzufügen']);
    }


    /**
     * @Route("/birthday/edit/{birthday}", name="birthday_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Birthday $birthday, Request $request)
    {
        $form = $this->createForm(BirthdayFormType::class, $birthday);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($birthday);
            $em->flush();
            $this->addFlash('success', 'Geburtstag wurde gespeichert!');
            return $this->redirectToRoute('birthday');
        }
        return $this->render('birthday/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Geburtstag bearbeiten']);
    }

    /**
     * @Route("/birthday/delete/{birthday}/{confirm}", name="birthday_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Birthday $birthday, $confirm=false, Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Geburtstag']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($birthday);
        $em->flush();
        $this->addFlash('success', 'Geburtstag wurde gelöscht!');
        return $this->redirectToRoute('birthday');

    }
}
