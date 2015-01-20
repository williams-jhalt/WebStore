<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Weborder;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/weborders")
 */
class WebordersController extends Controller {

    /**
     * @Route("/", name="weborders_index")
     * @Template("weborders/index.html.twig")
     */
    public function indexAction(Request $request) {

        $page = $request->get('page', 1);

        $repository = $this->getDoctrine()->getRepository("AppBundle:Weborder");

        $qb = $repository->createQueryBuilder('w');
        $qb->orderBy('w.orderDate', 'DESC');
                        
        $qb->where('w.customerNumber IN (:customerNumbers)');
        $qb->setParameter('customerNumbers', $this->getUser()->getCustomerNumbers());

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 50
        );

        return array('pagination' => $pagination);
    }
    
    /**
     * @Route("/submit", name="weborders_submit")
     * @Template("weborders/submit.html.twig")
     */
    public function submitAction(Request $request) {

        $weborder = new Weborder();

        $formBuilder = $this->createFormBuilder($weborder);
        
        $customerNumbers = $this->getUser()->getCustomerNumbers();
        
        if (sizeof($customerNumbers) > 1) {
            $options = array();
            foreach ($customerNumbers as $customerNumber) {
                $options[$customerNumber] = $customerNumber;                
            }
            $formBuilder->add('customerNumber', 'choice', array('label' => 'Customer Account', 'choices' => $options));
        } else {
            $weborder->setCustomerNumber($customerNumbers[0]);
        }
        
        $form = $formBuilder->add('reference1', 'text', array('label' => 'Customer Reference (PO#)', 'required' => false))
                ->add('reference2', 'text', array('label' => 'Customer Reference 2', 'required' => false))
                ->add('reference3', 'text', array('label' => 'Customer Reference 3', 'required' => false))
                ->add('shipToFirstName', 'text', array('label' => 'First Name', 'required' => false))
                ->add('shipToLastName', 'text', array('label' => 'Last Name', 'required' => true))
                ->add('shipToAddress1', 'text', array('label' => 'Address', 'required' => true))
                ->add('shipToAddress2', 'text', array('label' => 'Address (Cont.)', 'required' => false))
                ->add('shipToCity', 'text', array('label' => 'City', 'required' => true))
                ->add('shipToState', 'text', array('label' => 'State', 'required' => false))
                ->add('shipToZip', 'text', array('label' => 'Zip', 'required' => false))
                ->add('shipToCountry', 'country', array('label' => 'Country', 'required' => true, 'preferred_choices' => array('US')))
                ->add('shipToPhone', 'text', array('label' => 'Phone', 'required' => false))
                ->add('shipToEmail', 'email', array('label' => 'Email', 'required' => false))
                ->add('rush', 'checkbox', array('label' => 'Rush Order', 'required' => false))
                ->add('save', 'submit', array('label' => 'Submit Order'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            
            // this is where the order will be submitted to DistOne
            $weborder->setOrderNumber(rand(1000000,9999999)); # just some test data
            
            $weborder->setOrderDate(new DateTime());

            $em = $this->getDoctrine()->getManager();
            
            $em->persist($weborder);
            $em->flush();

            return $this->redirectToRoute('weborders_index');
        }

        return array('form' => $form->createView());
        
    }
    
    /**
     * @Route("/cartSummary", name="weborders_cart_summary")
     * @Template("weborders/cart_summary.html.twig")
     */
    public function cartSummaryAction() {

        $cartItems = $this->getUser()->getCartItems();

        return array('cartItems' => $cartItems);
        
    }
    
    /**
     * @Route("/import", name="weborders_import")
     * @Template("weborders/import.html.twig")
     */
    public function importAction() {
        
        return array();
        
    }
    
    /**
     * @Route("/export", name="weborders_export")
     * @Template("weborders/export.html.twig")
     */
    public function exportAction() {
        
        return array();
        
    }

}
