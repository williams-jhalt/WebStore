<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Category;
use AppBundle\Entity\Manufacturer;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use DateTime;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAware;

class LoadProductData extends ContainerAware implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        
        $manufacturer = new Manufacturer();
        $manufacturer->setCode('TE');
        $manufacturer->setName('Test Manufacturer');
        $manufacturer->setShowInMenu(true);
        
        $type = new ProductType();
        $type->setCode('TE');
        $type->setName('Test Type');
        $type->setShowInMenu(true);
        
        $category = new Category();
        $category->setCode('TE');
        $category->setName('Test Category');
        $category->setShowInMenu(true);
        
        $product = new Product();
        $product->setSku("TEST001");
        $product->setName("Test Product");
        $product->setBarcode("1234567890");
        $product->setPrice(1.23);
        $product->setReleaseDate(new DateTime());
        $product->setStockQuantity(123);
        $product->setShown(true);
        $product->setActive(true);
        $product->setManufacturer($manufacturer);
        $product->setProductType($type);
        $product->setCategories(array($category));
        
        $manager->persist($product);
        $manager->flush();
        
        
    }
}