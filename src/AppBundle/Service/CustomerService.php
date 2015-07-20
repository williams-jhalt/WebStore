<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class CustomerService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    public function findAll($offset = 0, $limit = 100) {
        
        
        $response = $this->erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = 'WTC'", "*", $offset, $limit
        );
                
        $repository = $this->em->getRepository('AppBundle:Customer');
                
        $customers = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $item) {
            $customer = $repository->findOrUpdate(array(
                'customerNumber' => $item->customer
            ));
            $customers[] = $customer;
        }
        
        $this->em->commit();

        return $customers;
    }

    public function get($customerNumber) {

        $response = $this->erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = 'WTC' AND customer = '{$customerNumber}'", "*"
        );
                
        if (sizeof($response) == 0) {
            return null;
        }
                
        $repository = $this->em->getRepository('AppBundle:Customer');
                
        $customers = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $orderObj) {
            
            $customer = $repository->findOrUpdate(array(
                'customerNumber' => $orderObj->customer
            ));
                        
            $customers[] = $customer;
        }
        
        $this->em->commit();

        return $customers[0];
    }

}
