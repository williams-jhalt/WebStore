<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository {
    
    public function findOrCreateByCode($code) {
        
        $category = $this->findOneByCode($code);
        
        if (!$category) {
            $category = new Category();
            $category->setCode($code);
            $category->setName($code);
            $this->getEntityManager()->persist($category);
            $this->getEntityManager()->flush();
        }
        
        return $category;
        
    }    
    
}