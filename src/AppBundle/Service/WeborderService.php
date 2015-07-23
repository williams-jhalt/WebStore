<?php

namespace AppBundle\Service;

use AppBundle\Entity\Weborder;
use AppBundle\Entity\WeborderAudit;
use AppBundle\Entity\WeborderItem;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class WeborderService {

    private $em;
    private $erp;
    private $erpOrderSelect = "order, customer, created_date, cu_po, ship_atn, name, state, postal_code, country_code, adr, stat";

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    private function _getDbRecordFromErp($item) {

        $data = array(
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
        );

        $repository = $this->em->getRepository('AppBundle:Weborder');

        return $repository->findOrCreate($data);
        
    }

    public function findAll($offset = 0, $limit = 100) {

        $rep = $this->em->getRepository('AppBundle:Weborder');

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' BY order DESCENDING", $this->erpOrderSelect, $offset, $limit
        );
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function findBySearchTerms($searchTerms, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}' OR customer BEGINS '{$searchTerms}')", $this->erpOrderSelect, $offset, $limit
        );
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function findByCustomerAndSearchTerms($customerNumber, $searchTerms, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND customer = '{$customerNumber}' "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}')", $this->erpOrderSelect, $offset, $limit
        );
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function findByCustomer($customerNumber, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND customer = '{$customerNumber}' BY order DESCENDING", $this->erpOrderSelect, $offset, $limit
        );
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

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

        $response = $this->erp->read($query, $this->erpOrderSelect, $offset, $limit);
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

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

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND ({$customerSelect}) BY order DESCENDING", $this->erpOrderSelect, $offset, $limit
        );
        
        $weborders = array();
        
        $this->em->beginTransaction();
        
        foreach ($response as $item) {
            
            $weborders[] = $this->_getDbRecordFromErp($item);
            
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function get($orderNumber) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$orderNumber}'", $this->erpOrderSelect
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_getDbRecordFromErp($response[0]);
    }

}
