<?php

namespace AppBundle\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class Builder {

    private $factory;

    public function __construct(FactoryInterface $factory) {
        $this->factory = $factory;
    }

    public function mainMenu(AuthorizationChecker $checker) {

        $menu = $this->factory->createItem("root");

        $menu->addChild('Home', array('route' => 'homepage'));

        if ($checker->isGranted('ROLE_USER')) {
            $menu->addChild('Catalog', array('route' => 'catalog_list'));
        }

        if ($checker->isGranted('ROLE_CUSTOMER')) {
            $menu->addChild('Cart', array('route' => 'cart_index'));
            $menu->addChild('Weborders', array('route' => 'weborders_index'));
        }
        
        if ($checker->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Admin', array('route' => 'admin_index'));
        }

        if ($checker->isGranted('ROLE_USER')) {
            $menu->addChild('Account', array('route' => 'account_index'));
        }

        return $menu;
    }

    public function categoryMenu(EntityManager $em) {

        $menu = $this->factory->createItem("root");

        $menu->addChild('All Categories', array('route' => 'catalog_list'));

        $categoryRepository = $em->getRepository('AppBundle:Category');

        $categories = $categoryRepository->findByShowInMenu(true);

        foreach ($categories as $category) {
            
            $count = $em->getRepository('AppBundle:Product')->countByCategoryAndShown($category, true);

            $menu->addChild($category->getName() . " ({$count})", array(
                'route' => 'catalog_list',
                'routeParameters' => array(
                    'categoryId' => $category->getId()
            )));
        }
        
        $menu->addChild("... more ...", array('route' => 'catalog_categories'));

        return $menu;
    }

    public function manufacturerMenu(EntityManager $em) {

        $menu = $this->factory->createItem("root");

        $menu->addChild('All Manufacturers', array('route' => 'catalog_list'));

        $repository = $em->getRepository('AppBundle:Manufacturer');

        $manufacturers = $repository->findByShowInMenu(true);

        foreach ($manufacturers as $manufacturer) {
            
            $count = $em->getRepository('AppBundle:Product')->countByManufacturerAndShown($manufacturer, true);

            $menu->addChild($manufacturer->getName() . " ({$count})", array(
                'route' => 'catalog_list',
                'routeParameters' => array(
                    'manufacturerId' => $manufacturer->getId()
            )));
        }
        
        $menu->addChild("... more ...", array('route' => 'catalog_manufacturers'));

        return $menu;
    }

    public function productTypeMenu(EntityManager $em) {

        $menu = $this->factory->createItem("root");

        $menu->addChild('All Product Types', array('route' => 'catalog_list'));

        $repository = $em->getRepository('AppBundle:ProductType');

        $productTypes = $repository->findByShowInMenu(true);

        foreach ($productTypes as $productType) {
            
            $count = $em->getRepository('AppBundle:Product')->countByProductTypeAndShown($productType, true);

            $menu->addChild($productType->getName() . " ({$count})", array(
                'route' => 'catalog_list',
                'routeParameters' => array(
                    'productTypeId' => $productType->getId()
            )));
        }
        
        $menu->addChild("... more ...", array('route' => 'catalog_product_types'));

        return $menu;
    }

    public function adminMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('Overview', array('route' => 'admin_index'));
        $menu->addChild('Products', array('route' => 'admin_list_products'));
        $menu->addChild('Product Attachments', array('route' => 'admin_list_product_attachments'));
        $menu->addChild('Manufacturers', array('route' => 'admin_list_manufacturers'));
        $menu->addChild('Categories', array('route' => 'admin_list_categories'));
        $menu->addChild('Product Types', array('route' => 'admin_list_product_types'));
        $menu->addChild('Users', array('route' => 'admin_list_users'));

        return $menu;
    }
    
    public function webordersMenu() {
        
        $menu = $this->factory->createItem("root");
        
        $menu->addChild('List Orders', array('route' => 'weborders_index'));
        $menu->addChild('Submit Order', array('route' => 'weborders_submit'));
        $menu->addChild('Import Orders', array('route' => 'weborders_import'));
        $menu->addChild('Export Orders', array('route' => 'weborders_export'));
        
        return $menu;
        
    }

}
