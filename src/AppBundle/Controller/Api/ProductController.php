<?php

namespace AppBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ProductController extends FOSRestController {
    
    /**
     * @Rest\Get("/product.{_format}")
     * @Rest\View
     */
    public function listAction(Request $request) {
        
        $page = (int) $request->get('page', 1);
        
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        
        $qb = $repository->createQueryBuilder('p');

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
     * @Rest\Get("/product/{id}")
     * @Rest\View
     */
    public function getAction($id) {
        
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        
        $products = $repository->find($id);
        
        $view = $this->view($products, 200);
        
        return $this->handleView($view);
        
    }
    
}