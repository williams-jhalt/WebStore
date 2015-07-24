<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class ShipmentService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
    }

    private function _loadFromErp($item) {

        $repository = $this->_em->getRepository('AppBundle:Shipment');
        $weborderRep = $this->_em->getRepository('AppBundle:Weborder');
        $weborder = $weborderRep->findOneByOrderNumber($item->order);

        return $repository->findOrUpdate(array(
                    'weborder' => $weborder,
                    'orderNumber' => $item->order,
                    'customerNumber' => $item->customer,
                    'status' => $item->stat,
                    'shipped' => $item->shipped
        ));
    }

    public function findAll($offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'S' BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();
        
        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }
        
        $this->_em->commit();

        return $weborders;
    }

    public function findByCustomer($customerNumber, $offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'S' AND customer = '{$customerNumber}' BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();
        
        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }
        
        $this->_em->commit();

        return $weborders;
    }

    public function findByCustomerNumbers(array $customerNumbers, $offset = 0, $limit = 100) {

        $customerSelect = "";
        for ($i = 0; $i < count($customerNumbers); $i++) {
            $customerSelect .= " customer = '{$customerNumbers[$i]}' ";
            if ($i < count($customerNumbers) - 1) {
                $customerSelect .= " OR ";
            }
        }

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'S' "
                . "AND ({$customerSelect}) "
                . "BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();
        
        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }
        
        $this->_em->commit();

        return $weborders;
    }

    public function get($orderNumber) {

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'S' AND order = '{$orderNumber}'", "*"
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_loadFromErp($response[0]);
    }

}
