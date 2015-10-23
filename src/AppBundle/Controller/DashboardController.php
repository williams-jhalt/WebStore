<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller {
    
    /**
     * @Route("/", name="dashboard_index")
     */
    public function indexAction() {
        return $this->render('AppBundle:Dashboard:index.html.twig');
    }
    
    /**
     * @Route("/new-items/{page}", name="dashboard_new_items", options={"expose": true})
     */
    public function newItemsAction($page = 1) {
        
        $perPage = 10;
        
        $rep = $this->getDoctrine()->getRepository('AppBundle:Product');
        
        $products = $rep->findBy(array(), array('releaseDate' => 'DESC'), $perPage, (($page - 1) * $perPage));
        
        if (sizeof($products) == $perPage) {
            $nextPage = $this->generateUrl('dashboard_new_items', array('page' => $page + 1));
        } else {
            $nextPage = "#";
        }
        
        return $this->render('AppBundle:Dashboard:newItems.html.twig', array(
            'products' => $products,
            'nextPage' => $nextPage
        ));
        
    }
    
    /**
     * @Route("/open-orders/{page}", name="dashboard_open_orders", options={"expose": true})
     */
    public function openOrdersAction($page = 1) {
        
        $perPage = 10;
        
        $rep = $this->getDoctrine()->getRepository('AppBundle:SalesOrder');
        
        $params = array(
            'open' => true
        );

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            $params['customerNumber'] = $this->getUser()->getCustomerNumbers();
        }
        
        $orders = $rep->findBy($params, array('orderNumber' => 'DESC'), $perPage, (($page - 1) * $perPage));
        
        if (sizeof($orders) == $perPage) {
            $nextPage = $this->generateUrl('dashboard_open_orders', array('page' => $page + 1));
        } else {
            $nextPage = "#";
        }
        
        return $this->render('AppBundle:Dashboard:openOrders.html.twig', array(
            'orders' => $orders,
            'nextPage' => $nextPage
        ));
        
    }

}
