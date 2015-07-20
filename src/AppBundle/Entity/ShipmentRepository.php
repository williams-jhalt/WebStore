<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ShipmentRepository extends EntityRepository {

    public function findOrUpdate(array $data) {
        
        $shipment = $this->findOneBy(array('orderNumber' => $data['orderNumber']));
        
        if (!$shipment) {
            $shipment = new Shipment();
        }

        $shipment->setOrderNumber($data['orderNumber']);
        $shipment->setCustomerNumber($data['customerNumber']);
        $shipment->setStatus($data['status']);
        
        $this->getEntityManager()->persist($shipment);
        $this->getEntityManager()->flush();

        return $shipment;
    }

}