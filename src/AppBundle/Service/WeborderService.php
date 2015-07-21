<?php

namespace AppBundle\Service;

use AppBundle\Entity\Weborder;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class WeborderService {

    private $em;
    private $erp;
    private $auditService;
    private $erpOrderSelect = "order, customer, created_date, cu_po, ship_atn, name, state, postal_code, country_code, adr, stat";

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, WeborderAuditService $auditService) {
        $this->em = $em;
        $this->erp = $erp;
        $this->auditService = $auditService;
    }

    private function _getDbRecordFromErp($item, $products = false) {

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
        
        $weborder = $repository->findOrCreate($data);

        // only update the audit if the record is older than 5 minutes, or has been created within the last 5 minutes
        if ($weborder->getUpdatedOn() < new DateTime("-5 minute") || $weborder->getCreatedOn() > new DateTime("-5 minute")) {

            $weborder->setStatus($data['status']);

            $this->em->persist($weborder);

            $weborderAuditRepository = $this->em->getRepository('AppBundle:WeborderAudit');

            $auditResponse = $this->erp->read("FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC' AND order = '{$item->order}'", "order, comment, rec_type, stat, stat_date, stat_ttime");

            $audits = array();

            foreach ($auditResponse as $item) {

                $timeStr = str_pad($item->stat_ttime, 6, "0", STR_PAD_LEFT);
                $dateStr = $item->stat_date;
                $timestamp = DateTime::createFromFormat("Y-m-d His", "{$dateStr} {$timeStr}");

                $audits[] = $weborderAuditRepository->findOrCreate(array(
                    'weborder' => $weborder,
                    'orderNumber' => $item->order,
                    'comment' => $item->comment,
                    'recordType' => $item->rec_type,
                    'statusCode' => $item->stat,
                    'timestamp' => $timestamp
                ));
            }
            
        }

        // if we're just listing, don't load the products
        if ($products) {

            $weborderItemRepository = $this->em->getRepository('AppBundle:WeborderItem');

            $itemResponse = $this->erp->read("FOR EACH oe_line NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$item->order}'", "order, item, q_ord");

            $items = array();

            foreach ($itemResponse as $itemObj) {
                $items[] = $weborderItemRepository->findOrCreate(array(
                    'weborder' => $weborder,
                    'orderNumber' => $itemObj->order,
                    'sku' => $itemObj->item,
                    'quantity' => $itemObj->q_ord
                ));
            }
            
        }

        return $weborder;
    }

    public function findAll($offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' BY oe_head.order DESCENDING", $this->erpOrderSelect, $offset, $limit
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
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}' OR customer BEGINS '{$searchTerms}') BY oe_head.order DESCENDING", $this->erpOrderSelect, $offset, $limit
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
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}') BY oe_head.order DESCENDING", $this->erpOrderSelect, $offset, $limit
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
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND customer = '{$customerNumber}' BY oe_head.order DESCENDING", $this->erpOrderSelect, $offset, $limit
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
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}') BY oe_head.order DESCENDING";

        $response = $this->erp->read($query, $this->erpOrderSelect, $offset, $limit
        );

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
                . "AND ({$customerSelect}) BY oe_head.order DESCENDING", $this->erpOrderSelect, $offset, $limit
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

        return $this->_getDbRecordFromErp($response[0], true);
    }

    public function batchUpdate(OutputInterface $output) {

        $repository = $this->em->getRepository('AppBundle:Weborder');

        try {
            $qb = $repository->createQueryBuilder('w');

            $lastOrder = $qb->orderBy('w.orderNumber', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();

            $lastOrderNumber = $lastOrder->getOrderNumber();
        } catch (Exception $e) {
            $lastOrderNumber = 0;
        }

        $offset = 0;
        $limit = 500;

        do {

            $end = $offset + $limit;

            $output->writeln("Processing records {$offset} to {$end}");

            $response = $this->erp->read(
                    "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order GT '{$lastOrderNumber}'", $this->erpOrderSelect, $offset, $limit
            );

            $this->em->beginTransaction();

            foreach ($response as $item) {

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

                $repository->findOrCreate($data);
            }

            $this->em->commit();

            $offset = $end;
        } while (!empty($response));
    }

}
