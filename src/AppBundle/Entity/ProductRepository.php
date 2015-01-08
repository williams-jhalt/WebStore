<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository {
    
    public function findBySearchTerms($searchTerms) {
        return $this->getEntityManager()
                ->createQuery("SELECT p "
                        . "FROM AppBundle:Product p "
                        . "WHERE p.sku LIKE :sku "
                        . "OR p.name LIKE :name")
                ->setParameter('sku', "{$searchTerms}%")
                ->setParameter('name', "%{$searchTerms}%")
                ->getResult();
    }   
    
    public function countByCategory($category) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p JOIN p.categories c WITH c.id = :id";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('id', $category->getId())
                ->getSingleScalarResult();
        
    }
    
    public function countByCategoryAndShown($category, $shown) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p JOIN p.categories c WITH c.id = :id WHERE p.shown = :shown";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('id', $category->getId())
                ->setParameter('shown', $shown)
                ->getSingleScalarResult();
        
    }
    
    public function countByManufacturer($manufacturer) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p WHERE p.manufacturer = :manufacturer";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('manufacturer', $manufacturer)
                ->getSingleScalarResult();
        
    } 
    
    public function countByManufacturerAndShown($manufacturer, $shown) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p WHERE p.manufacturer = :manufacturer AND p.shown = :shown";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('manufacturer', $manufacturer)
                ->setParameter('shown', $shown)
                ->getSingleScalarResult();
        
    }
    
    public function countByProductType($productType) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p WHERE p.productType = :productType";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('productType', $productType)
                ->getSingleScalarResult();
        
    }
    
    public function countByProductTypeAndShown($productType, $shown) {
        
        $dql = "SELECT COUNT(p.id) FROM AppBundle:Product p WHERE p.productType = :productType AND p.shown = :shown";
        
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('productType', $productType)
                ->setParameter('shown', $shown)
                ->getSingleScalarResult();
        
    }
    
}