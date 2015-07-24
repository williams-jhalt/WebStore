<?php

namespace AppBundle\Service;

use AppBundle\Entity\Weborder;
use DateTime;
use Doctrine\ORM\EntityManager;

class WeborderService {

    private $_em;
    private $_erp;
    private $_erpSelect = "order, customer, created_date, cu_po, ship_atn, name, state, postal_code, country_code, adr, stat";
    private $_weborderItemService;
    private $_weborderAuditService;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, WeborderItemService $weborderItemService, WeborderAuditService $weborderAuditService) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_weborderItemService = $weborderItemService;
        $this->_weborderAuditService = $weborderAuditService;
    }

    private function _loadFromErp($item) {

        $repository = $this->_em->getRepository('AppBundle:Weborder');

        $weborder = $repository->findOrUpdate(array(
            'orderNumber' => $item->order,
            'customerNumber' => $item->customer,
            'orderDate' => new DateTime($item->created_date),
            'reference1' => $item->cu_po,
            'shipToAttention' => $item->ship_atn,
            'shipToCompany' => $item->name,
            'shipToState' => $item->state,
            'shipToZip' => $item->postal_code,
            'shipToCountry' => $item->country_code,
            'shipToAddress1' => $item->adr[0],
            'shipToAddress2' => $item->adr[1],
            'shipToAddress3' => $item->adr[2],
            'shipToCity' => $item->adr[4],
            'status' => $item->stat
        ));

        if (empty($weborder->getItems())) {
            $weborder->setItems(
                    $this->_weborderItemService->findByOrderNumber(
                            $weborder->getOrderNumber()));
        }

        if (empty($weborder->getAudits())) {
            $weborder->setAudits(
                    $this->_weborderAuditService->findByOrderNumber(
                            $weborder->getOrderNumber()));
        }
        
        return $weborder;
    }

    public function findAll($offset = 0, $limit = 100) {

        $rep = $this->_em->getRepository('AppBundle:Weborder');

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' BY order DESCENDING", $this->_erpSelect, $offset, $limit
        );

        $weborders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {

            $weborders[] = $this->_loadFromErp($item);
        }

        $this->_em->commit();

        return $weborders;
    }

    public function findBySearchTerms($searchTerms, $offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}' OR customer BEGINS '{$searchTerms}')", $this->_erpSelect, $offset, $limit
        );

        $weborders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {

            $weborders[] = $this->_loadFromErp($item);
        }

        $this->_em->commit();

        return $weborders;
    }

    public function findByCustomerAndSearchTerms($customerNumber, $searchTerms, $offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND customer = '{$customerNumber}' "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}')", $this->_erpSelect, $offset, $limit
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
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND customer = '{$customerNumber}' BY order DESCENDING", $this->_erpSelect, $offset, $limit
        );

        $weborders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {

            $weborders[] = $this->_loadFromErp($item);
        }

        $this->_em->commit();

        return $weborders;
    }

    public function findByCustomerNumbersAndSearchTerms(array $customerNumbers, $searchTerms, $offset = 0, $limit = 100) {

        $customerSelect = "";
        for ($i = 0; $i < count($customerNumbers); $i++) {
            $customerSelect .= " customer = '{$customerNumbers[$i]}' ";
            if ($i < count($customerNumbers) - 1) {
                $customerSelect .= " OR ";
            }
        }

        $query = "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND ({$customerSelect}) "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}')";

        $response = $this->_erp->read($query, $this->_erpSelect, $offset, $limit);

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
                . "AND rec_type = 'O' "
                . "AND ({$customerSelect}) BY order DESCENDING", $this->_erpSelect, $offset, $limit
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
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$orderNumber}'", $this->_erpSelect
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_loadFromErp($response[0]);
    }

}
