<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Weborder;
use DateInterval;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class WeborderController extends FOSRestController {
    
    /**
     * @Rest\Get("/weborder")
     * @Rest\View
     */
    public function listAction(Request $request) {
        
        $now = new DateTime();
        
        $page = (int) $request->get('page', 1);
        $dateStart = $request->get('dateStart', $now->format('c'));
        $dateEnd = $request->get('dateEnd', $now->sub(new DateInterval("P1M"))->format('c'));
        
        $repository = $this->getDoctrine()->getRepository('AppBundle:Weborder');

        $qb = $repository->createQueryBuilder('w');
        
        $qb->where('w.orderDate BETWEEN :start AND :end')
                ->setParameter('start', new DateTime($dateStart))
                ->setParameter('end', new DateTime($dateEnd));

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 25
        );
        
        $response = array(
            'page' => $pagination->getCurrentPageNumber(),
            'perPage' => $pagination->getItemNumberPerPage(),
            'total' => $pagination->getTotalItemCount(),
            'items' => $pagination->getItems()
        );
        
        $view = $this->view($response, 200);
        
        return $this->handleView($view);
        
    }
    
    /**
     * @Rest\Get("/weborder/{id}")
     * @Rest\View
     */
    public function getAction($id) {
        
        $repository = $this->getDoctrine()->getRepository('AppBundle:Weborder');
        
        $weborder = $repository->find($id);
        
        $view = $this->view($weborder, 200);
        
        return $this->handleView($view);
        
    }    
    
    /**
     * @Rest\Post("/weborder")
     * @Rest\View
     */
    public function cpostAction(Request $request) {
        
        $weborder = new Weborder();
        
        $form = $this->getForm($weborder);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($weborder);
            $em->flush();
            
        }
        
        $response = array('id' => $weborder->getId());
        
        $view = $this->view($response, 200);
        
        return $this->handleView($view);
        
    }
    
    public function getForm($weborder = null) {
        
        return $this->createForm(new Weborder(), $weborder);
        
    }
    
}