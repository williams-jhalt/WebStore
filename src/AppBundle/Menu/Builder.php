<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware {

    public function mainMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('Home', array('route' => 'homepage'));
        
        $checker = $this->container->get('security.authorization_checker');

        if ($checker->isGranted('ROLE_USER')) {
            $menu->addChild('Catalog', array('route' => 'catalog_list'));
        }

        if ($checker->isGranted('ROLE_CUSTOMER')) {
            $menu->addChild('Cart', array('route' => 'cart_index'));
        }
        
        if ($checker->isGranted(array('ROLE_CUSTOMER', 'ROLE_ADMIN'))) {
            $weborders = $menu->addChild('Weborders');
            $weborders->addChild('Orders', array('route' => 'weborders_index'));
            $weborders->addChild('Invoices', array('route' => 'invoice_index'));
            $weborders->addChild('Shipments', array('route' => 'shipment_index'));
        }
        
        if ($checker->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Admin', array('route' => 'admin_index'));
        }

        return $menu;
    }

    public function accountMenu(FactoryInterface $factory, array $options) {
        $menu = $factory->createItem('root');

        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menu->addChild('My Account', array('route' => 'account_index'));
            $menu->addChild('Logout', array('route' => 'fos_user_security_logout'));
        } else {
            $menu->addChild('Login', array('route' => 'fos_user_security_login'));
        }

        return $menu;
    }

    public function categoryMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('All Categories', array('route' => 'catalog_list'));
        
        $em = $this->container->get('doctrine.orm.entity_manager');

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

    public function manufacturerMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('All Manufacturers', array('route' => 'catalog_list'));
        
        $em = $this->container->get('doctrine.orm.entity_manager');

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

    public function productTypeMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('All Product Types', array('route' => 'catalog_list'));
        
        $em = $this->container->get('doctrine.orm.entity_manager');

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

    public function adminMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('Overview', array('route' => 'admin_index'));
        $menu->addChild('Products', array('route' => 'admin_product_list'));
        $menu->addChild('Product Attachments', array('route' => 'admin_product_attachment_list'));
        $menu->addChild('Manufacturers', array('route' => 'admin_manufacturer_list'));
        $menu->addChild('Categories', array('route' => 'admin_category_list'));
        $menu->addChild('Product Types', array('route' => 'admin_product_type_list'));
        $menu->addChild('Users', array('route' => 'admin_user_list'));

        return $menu;
    }

    public function adminCategoryMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_category_list'));
        $menu->addChild('Add', array('route' => 'admin_category_add'));
        $menu->addChild('Import', array('route' => 'admin_category_import'));
        $menu->addChild('Show All', array('route' => 'admin_category_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_category_hide_all'));

        return $menu;
    }

    public function adminManufacturerMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_manufacturer_list'));
        $menu->addChild('Add', array('route' => 'admin_manufacturer_add'));
        $menu->addChild('Import', array('route' => 'admin_manufacturer_import'));
        $menu->addChild('Show All', array('route' => 'admin_manufacturer_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_manufacturer_hide_all'));

        return $menu;
    }

    public function adminProductAttachmentMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_attachment_list'));
        $menu->addChild('Add', array('route' => 'admin_product_attachment_add'));
        $menu->addChild('Import', array('route' => 'admin_product_attachment_import'));

        return $menu;
    }

    public function adminProductTypeMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_type_list'));
        $menu->addChild('Add', array('route' => 'admin_product_type_add'));
        $menu->addChild('Import', array('route' => 'admin_product_type_import'));
        $menu->addChild('Show All', array('route' => 'admin_product_type_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_product_type_hide_all'));

        return $menu;
    }

    public function adminProductMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_list'));
        $menu->addChild('Add', array('route' => 'admin_product_add'));
        
        $importMenu = $menu->addChild('Import');
        $importMenu->addChild('Products', array('route' => 'admin_product_import'));
        $importMenu->addChild('Details', array('route' => 'admin_product_import_details'));
        $importMenu->addChild('Descriptions', array('route' => 'admin_product_import_descriptions'));
        
        $menu->addChild('Syncronize', array('route' => 'admin_product_prepare_synchronize'));
        
        $menu->addChild('Show All', array('route' => 'admin_product_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_product_hide_all'));

        return $menu;
    }

    public function adminUserMenu(FactoryInterface $factory, array $options) {

        $menu = $factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_user_list'));
        $menu->addChild('Add', array('route' => 'admin_user_add'));

        return $menu;
    }
    
    public function webordersMenu(FactoryInterface $factory, array $options) {
        
        $menu = $factory->createItem("root");
        
        $menu->addChild('List Orders', array('route' => 'weborders_index'));
        $menu->addChild('Export Orders', array('route' => 'weborders_export'));
        
        return $menu;
        
    }
    
    public function invoiceMenu(FactoryInterface $factory, array $options) {
        
        $menu = $factory->createItem("root");
        
        $menu->addChild('List Invoices', array('route' => 'invoice_index'));
        $menu->addChild('Export Invoices', array('route' => 'invoice_export'));
        
        return $menu;
        
    }
    
    public function shipmentMenu(FactoryInterface $factory, array $options) {
        
        $menu = $factory->createItem("root");
        
        $menu->addChild('List Shipments', array('route' => 'shipment_index'));
        $menu->addChild('Export Shipments', array('route' => 'shipment_export'));
        
        return $menu;
        
    }

}
