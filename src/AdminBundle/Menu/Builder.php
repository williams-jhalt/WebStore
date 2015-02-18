<?php

namespace AdminBundle\Menu;

use Knp\Menu\FactoryInterface;

class Builder {

    private $factory;

    public function __construct(FactoryInterface $factory) {
        $this->factory = $factory;
    }

    public function adminMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('Overview', array('route' => 'admin_index'));
        $menu->addChild('Products', array('route' => 'admin_product_list'));
        $menu->addChild('Product Attachments', array('route' => 'admin_product_attachment_list'));
        $menu->addChild('Manufacturers', array('route' => 'admin_manufacturer_list'));
        $menu->addChild('Categories', array('route' => 'admin_category_list'));
        $menu->addChild('Product Types', array('route' => 'admin_product_type_list'));
        $menu->addChild('Users', array('route' => 'admin_user_list'));

        return $menu;
    }

    public function adminCategoryMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_category_list'));
        $menu->addChild('Add', array('route' => 'admin_category_add'));
        $menu->addChild('Import', array('route' => 'admin_category_import'));
        $menu->addChild('Show All', array('route' => 'admin_category_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_category_hide_all'));

        return $menu;
    }

    public function adminManufacturerMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_manufacturer_list'));
        $menu->addChild('Add', array('route' => 'admin_manufacturer_add'));
        $menu->addChild('Import', array('route' => 'admin_manufacturer_import'));
        $menu->addChild('Show All', array('route' => 'admin_manufacturer_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_manufacturer_hide_all'));

        return $menu;
    }

    public function adminProductAttachmentMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_attachment_list'));
        $menu->addChild('Add', array('route' => 'admin_product_attachment_add'));
        $menu->addChild('Import', array('route' => 'admin_product_attachment_import'));

        return $menu;
    }

    public function adminProductTypeMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_type_list'));
        $menu->addChild('Add', array('route' => 'admin_product_type_add'));
        $menu->addChild('Import', array('route' => 'admin_product_type_import'));
        $menu->addChild('Show All', array('route' => 'admin_product_type_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_product_type_hide_all'));

        return $menu;
    }

    public function adminProductMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_product_list'));
        $menu->addChild('Add', array('route' => 'admin_product_add'));
        
        $importMenu = $menu->addChild('Import');
        $importMenu->addChild('Products', array('route' => 'admin_product_import'));
        $importMenu->addChild('Details', array('route' => 'admin_product_import_details'));
        $importMenu->addChild('Descriptions', array('route' => 'admin_product_import_descriptions'));
        
        $menu->addChild('Show All', array('route' => 'admin_product_show_all'));
        $menu->addChild('Hide All', array('route' => 'admin_product_hide_all'));

        return $menu;
    }

    public function adminUserMenu() {

        $menu = $this->factory->createItem("root");

        $menu->addChild('List', array('route' => 'admin_user_list'));
        $menu->addChild('Add', array('route' => 'admin_user_add'));

        return $menu;
    }

}
