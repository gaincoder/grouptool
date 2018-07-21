<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\News;
use AppBundle\Form\CommentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends Controller
{
    



    /**
     * @Route("/comment/edit/{comment}/{objecttype}/{object}", name="comment_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Comment $comment, $objecttype, $object, Request $request)
    {
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Kommentar wurde gespeichert!');
            return $this->redirectToRoute($objecttype.'_view',[$objecttype=>$object]);
        }
        return $this->render('comment/form.html.twig', ['form' => $form->createView(), 'page_title' => 'Kommentar bearbeiten']);
    }

    /**
     * @Route("/comment/delete/{comment}/{objecttype}/{object}/{confirm}", name="comment_delete",defaults={"confirm"=false})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Comment $comment, $objecttype, $object, $confirm=false, Request $request)
    {
        if($confirm == false){
            return $this->render('confirm.html.twig',['type' => 'Kommentar','objecttype'=>$objecttype,'object'=>$object]);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'Kommentar wurde gelöscht!');
        return $this->redirectToRoute($objecttype.'_view',[$objecttype=>$object]);

    }

}
