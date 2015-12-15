<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/sales-order")
 */
class SalesOrderController extends Controller {
    
    /**
     * @Route("/", name="admin_sales_order_index")
     * @Template("AppBundle:Admin/SalesOrder:index.html.twig")
     */
    public function indexAction(Request $request) {
        return array();
    }
    
    /**
     * @Route("/missing-tracking", name="admin_sales_order_missing_tracking")
     * @Template("AppBundle:Admin/SalesOrder:missing_tracking.html.twig")
     */
    public function missingTrackingAction(Request $request) {
        
        $dql = "SELECT o FROM AppBundle:SalesOrder o WHERE o.invoices IS NOT EMPTY AND o.packages IS EMPTY";
        
        $orders = $this->getDoctrine()->getManager()->createQuery($dql)->getResult();
        
        return array('orders' => $orders);
    }
    
}