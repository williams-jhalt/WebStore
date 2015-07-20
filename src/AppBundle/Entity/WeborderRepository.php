<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeborderRepository extends EntityRepository {

    public function findOrUpdate(array $data) {

        $weborder = $this->findOneBy(array('orderNumber' => $data['orderNumber']));

        if (!$weborder) {
            $weborder = new Weborder();
        }

        $weborder->setOrderNumber($data['orderNumber']);
        $weborder->setCustomerNumber($data['customerNumber']);
        $weborder->setOrderDate($data['orderDate']);
        $weborder->setReference1($data['reference1']);
        $weborder->setShipToAttention($data['shipToAttention']);
        $weborder->setShipToCompany($data['shipToCompany']);
        $weborder->setShipToState($data['shipToState']);
        $weborder->setShipToZip($data['shipToZip']);
        $weborder->setShipToCountry($data['shipToCountry']);
        $weborder->setShipToCity($data['shipToCity']);
        $weborder->setShipToAddress1($data['shipToAddress1']);
        $weborder->setShipToAddress2($data['shipToAddress2']);
        $weborder->setShipToAddress3($data['shipToAddress3']);
        $weborder->setStatus($data['status']);

        $this->getEntityManager()->persist($weborder);
        $this->getEntityManager()->flush();

        return $weborder;
    }

    public function findOrCreate(array $data) {

        $weborder = $this->findOneBy(array('orderNumber' => $data['orderNumber']));

        if (!$weborder) {
            $weborder = new Weborder();
            $weborder->setOrderNumber($data['orderNumber']);
            $weborder->setCustomerNumber($data['customerNumber']);
            $weborder->setOrderDate($data['orderDate']);
            $weborder->setReference1($data['reference1']);
            $weborder->setShipToAttention($data['shipToAttention']);
            $weborder->setShipToCompany($data['shipToCompany']);
            $weborder->setShipToState($data['shipToState']);
            $weborder->setShipToZip($data['shipToZip']);
            $weborder->setShipToCountry($data['shipToCountry']);
            $weborder->setShipToCity($data['shipToCity']);
            $weborder->setShipToAddress1($data['shipToAddress1']);
            $weborder->setShipToAddress2($data['shipToAddress2']);
            $weborder->setShipToAddress3($data['shipToAddress3']);
            $weborder->setStatus($data['status']);
            $this->getEntityManager()->persist($weborder);
            $this->getEntityManager()->flush();
        }

        return $weborder;
    }

}
