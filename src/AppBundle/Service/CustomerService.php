<?php

namespace AppBundle\Service;

use AppBundle\Entity\Customer;
use Doctrine\ORM\EntityManager;

class CustomerService {

    private $_em;
    private $_erp;
    private $_company;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    public function _loadFromErp($item) {

        $repository = $this->_em->getRepository('AppBundle:Customer');
        
        $customer = $repository->findOneByCustomerNumber($item->customer);
        
        if ($customer === null) {
            $customer = new Customer();
        }
        
        $customer->setCustomerNumber($item->customer);
        
        $this->_em->persist($customer);
        $this->_em->flush();
        
        return $customer;
        
    }

    public function findAll($offset = 0, $limit = 100) {


        $response = $this->_erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = '{$this->_company}'", "customer", $offset, $limit
        );

        $customers = array();

        foreach ($response as $item) {
            $customers[] = $this->_loadFromErp($item);
        }
        
        return $customers;
    }

    public function get($customerNumber) {

        $response = $this->_erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = '{$this->_company}' AND customer = '{$customerNumber}'", "customer"
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_loadFromErp($response[0]);
    }

}
