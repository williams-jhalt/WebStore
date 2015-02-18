<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    /**
     * @Route("/", name="admin_index")
     * @Template("AdminBundle:Default:index.html.twig")
     */
    public function indexAction() {

        $em = $this->getDoctrine()->getManager();

        $numberOfProducts = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Product p')->getSingleScalarResult();
        $numberOfCategories = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Category p')->getSingleScalarResult();
        $numberOfManufacturers = $em->createQuery('SELECT COUNT(p) FROM AppBundle:Manufacturer p')->getSingleScalarResult();
        $numberOfUsers = $em->createQuery('SELECT COUNT(p) FROM AppBundle:User p')->getSingleScalarResult();
        $numberOfProductTypes = $em->createQuery('SELECT COUNT(p) FROM AppBundle:ProductType p')->getSingleScalarResult();

        return array(
            'number_of_products' => $numberOfProducts,
            'number_of_categories' => $numberOfCategories,
            'number_of_manufacturers' => $numberOfManufacturers,
            'number_of_product_types' => $numberOfProductTypes,
            'number_of_users' => $numberOfUsers
        );
    }

}
