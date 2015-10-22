<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    /**
     * @Route("/admin", name="admin_index")
     * @Template("AppBundle:Admin/Default:index.html.twig")
     */
    public function indexAction() {

        $em = $this->getDoctrine()->getManager();

        $numberOfProducts = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Product p')->getSingleScalarResult();
        $numberOfCategories = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Category p')->getSingleScalarResult();
        $numberOfManufacturers = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Manufacturer p')->getSingleScalarResult();
        $numberOfUsers = $em->createQuery('SELECT COUNT(p) FROM AppBundle:User p')->getSingleScalarResult();
        $numberOfProductTypes = $em->createQuery('SELECT COUNT(p) FROM AppBundle:ProductType p')->getSingleScalarResult();
        $numberOfOpenOrders = $em->createQuery('SELECT COUNT(o) FROM AppBundle:SalesOrder o WHERE open = true')->getSingleScalarResult();
        $ordersPerDay = $em->createQuery('SELECT COUNT(*) FROM AppBundle:SalesOrder o WHERE orderDate > DATE_SUB(orderDate, 5, DAY) GROUP BY orderDate')->getScalarResult();
        
        $avgOrdersPerDay = array_sum($ordersPerDay) / sizeof($ordersPerDay);
        

        return array(
            'number_of_products' => $numberOfProducts,
            'number_of_categories' => $numberOfCategories,
            'number_of_manufacturers' => $numberOfManufacturers,
            'number_of_product_types' => $numberOfProductTypes,
            'number_of_users' => $numberOfUsers,
            'number_of_open_orders' => $numberOfOpenOrders,
            'avg_orders_per_day' => $avgOrdersPerDay                
        );
    }

}
