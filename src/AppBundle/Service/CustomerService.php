<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class CustomerService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
    }

    public function _loadFromErp($item) {

        $repository = $this->_em->getRepository('AppBundle:Customer');

        return $repository->findOrCreate(array(
                    'customerNumber' => $item->customer
        ));
    }

    public function findAll($offset = 0, $limit = 100) {


        $response = $this->_erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = 'WTC'", "customer", $offset, $limit
        );

        $customers = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $customers[] = $this->_loadFromErp($item);
        }

        $this->_em->commit();

        return $customers;
    }

    public function get($customerNumber) {

        $response = $this->_erp->read(
                "FOR EACH customer NO-LOCK WHERE company_cu = 'WTC' AND customer = '{$customerNumber}'", "customer"
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_loadFromErp($response[0]);
    }

}
