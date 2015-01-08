<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Category;
use AppBundle\Entity\Manufacturer;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProductData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        
        $category = new Category();
        $category->setCode("TE");
        $category->setName("Test Category");
        
        $manager->persist($category);
        
        $category2 = new Category();
        $category2->setCode("TE2");
        $category2->setName("Test Category 2");
        
        $manager->persist($category2);
        
        $manufacturer = new Manufacturer();
        $manufacturer->setCode("TE");
        $manufacturer->setName("Test Manufacturer");
        
        $manager->persist($manufacturer);
        
        $manufacturer2 = new Manufacturer();
        $manufacturer2->setCode("TE2");
        $manufacturer2->setName("Test Manufacturer 2");
        
        $manager->persist($manufacturer2);
        
        $productType = new ProductType();
        $productType->setCode("TE");
        $productType->setName("Test Type");
        
        $manager->persist($productType);
        
        $product = new Product();
        $product->setSku("TEST001");
        $product->setName("Test Product");
        $product->addCategory($category);
        $product->setManufacturer($manufacturer);
        $product->setProductType($productType);
        
        $manager->persist($product);
        
        $product2 = new Product();
        $product2->setSku("TEST002");
        $product2->setName("Test Product 2");
        $product2->addCategory($category2);
        $product2->setManufacturer($manufacturer2);
        $product2->setProductType($productType);
        
        $manager->persist($product2);
        
        $product3 = new Product();
        $product3->setSku("TEST003");
        $product3->setName("Test Product 3");
        $product3->addCategory($category);
        $product3->setManufacturer($manufacturer2);
        $product3->setProductType($productType);
        
        $manager->persist($product3);
        
        $product4 = new Product();
        $product4->setSku("TEST004");
        $product4->setName("Test Product 4");
        $product4->addCategory($category2);
        $product4->setManufacturer($manufacturer);
        $product4->setProductType($productType);
        
        $manager->persist($product4);
        
        $manager->flush();
        
    }
}