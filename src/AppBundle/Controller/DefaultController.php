<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $params = array();

        $birthdayRepo = $this->getDoctrine()->getRepository('AppBundle:Birthday');
        $params['birthday_current_month'] = $birthdayRepo->findAllThisMonthOrderedByDay();
        $params['birthday_next_month'] = $birthdayRepo->findAllNextMonthOrderedByDay();


        $eventRepo = $this->getDoctrine()->getRepository('AppBundle:Event');
        $params['events'] = $eventRepo->findNextFive($this->isGranted('ROLE_STAMMI'));

        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        $params['newslist'] = $newsRepo->findTopFive($this->isGranted('ROLE_STAMMI'));


        $pollRepo = $this->getDoctrine()->getRepository('AppBundle:Poll');
        $params['polls'] = $pollRepo->findTopFive($this->isGranted('ROLE_STAMMI'));

        return $this->render('default/index.html.twig',$params);
    }
}
