<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductTypeRepository extends EntityRepository {
    
    public function findOrCreateByCode($code) {
        
        $productType = $this->findOneByCode($code);
        
        if (!$productType) {
            $productType = new ProductType();
            $productType->setCode($code);
            $productType->setName($code);
            $this->getEntityManager()->persist($productType);
            $this->getEntityManager()->flush();
        }
        
        return $productType;
        
    }    
    
}