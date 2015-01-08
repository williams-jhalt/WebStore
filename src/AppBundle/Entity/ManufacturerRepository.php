<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ManufacturerRepository extends EntityRepository {
    
    public function findOrCreateByCode($code) {
        
        $manufacturer = $this->findOneByCode($code);
        
        if (!$manufacturer) {
            $manufacturer = new Manufacturer();
            $manufacturer->setCode($code);
            $manufacturer->setName($code);
            $this->getEntityManager()->persist($manufacturer);
            $this->getEntityManager()->flush();
        }
        
        return $manufacturer;
        
    }    
    
}