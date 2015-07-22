<?php

namespace AppBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class WeborderService {

    private $em;
    private $erp;
    private $auditService;
    private $erpOrderSelect = "order, customer, created_date, cu_po, ship_atn, name, state, postal_code, country_code, adr, stat";

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
        $this->auditService = $auditService;
    }

    private function _getMultipleDbRecordsFromErp($response) {

        $orderNumbers = array();

        foreach ($response as $item) {
            $orderNumbers[] = $item->order;
        }

        $rep = $this->em->getRepository('AppBundle:Weborder');
        $weborders = $rep->findBy(array('orderNumber' => $orderNumbers));

        $knownOrderNumbers = array();

        foreach ($weborders as $weborder) {
            $knownOrderNumbers[] = $weborder->getOrderNumber();
        }

        $newOrderNumbers = array_diff($orderNumbers, $knownOrderNumbers);

        $weborders = array_reverse($weborders);

        $this->em->beginTransaction();

        foreach ($response as $item) {
            if (array_search($item->order, $newOrderNumbers) !== false) {

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

                array_push($weborders, $rep->findOrCreate($data));
            }
        }

        $this->em->commit();

        $weborders = array_reverse($weborders);

        return $weborders;
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

        $weborder = $repository->findOrCreate($data);

        $weborder->setStatus($data['status']);
        $this->em->persist($weborder);
        $this->em->flush($weborder);

        $weborderAuditRepository = $this->em->getRepository('AppBundle:WeborderAudit');

        try {

            $qb = $weborderAuditRepository->createQueryBuilder('a');
            $lastAudit = $qb->where('a.orderNumber = :orderNumber')
                    ->orderBy('a.recordDate,a.recordTime', 'DESC')
                    ->setMaxResults(1)
                    ->setParameter('orderNumber', $item->order)
                    ->getQuery()
                    ->getSingleResult();

            $auditResponse = $this->erp->read("FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC' AND order = '{$item->order}' AND stat_date >= '{$lastAudit->getRecordDate()}' AND stat_ttime > '{$lastAudit->getRecordTime()}'", "order, comment, rec_type, stat, stat_date, stat_ttime");
        } catch (\Exception $e) {
            $auditResponse = $this->erp->read("FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC' AND order = '{$item->order}'", "order, comment, rec_type, stat, stat_date, stat_ttime");
        }

        foreach ($auditResponse as $item) {
            $weborderAuditRepository->findOrCreate(array(
                'weborder' => $weborder,
                'recordDate' => $item->stat_date,
                'recordTime' => $item->stat_ttime,
                'orderNumber' => $item->order,
                'comment' => $item->comment,
                'recordType' => $item->rec_type,
                'statusCode' => $item->stat
            ));
        }

        $weborderItemRepository = $this->em->getRepository('AppBundle:WeborderItem');

        try {

            $qb = $weborderItemRepository->createQueryBuilder('a');
            $lastItem = $qb->where('a.orderNumber = :orderNumber')
                    ->orderBy('a.lineNumber', 'DESC')
                    ->setMaxResults(1)
                    ->setParameter('orderNumber', $item->order)
                    ->getQuery()
                    ->getSingleResult();
            $itemResponse = $this->erp->read("FOR EACH oe_line NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$item->order}' AND line > '{$lastItem->getLineNumber()}'", "order, item, q_ord, line");
        } catch (\Exception $e) {
            $itemResponse = $this->erp->read("FOR EACH oe_line NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$item->order}'", "order, item, q_ord, line");
        }

        foreach ($itemResponse as $itemObj) {
            $weborderItemRepository->findOrCreate(array(
                'weborder' => $weborder,
                'orderNumber' => $itemObj->order,
                'sku' => $itemObj->item,
                'lineNumber' => $itemObj->line,
                'quantity' => $itemObj->q_ord
            ));
        }

        return $weborder;
    }

    public function findAll($offset = 0, $limit = 100) {

        $rep = $this->em->getRepository('AppBundle:Weborder');

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' USE-INDEX order_d", $this->erpOrderSelect, $offset, $limit
        );

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

        return $weborders;
    }

    public function findBySearchTerms($searchTerms, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'O' "
                . "AND (STRING(order) BEGINS '{$searchTerms}' OR cu_po BEGINS '{$searchTerms}' OR customer BEGINS '{$searchTerms}')", $this->erpOrderSelect, $offset, $limit
        );

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

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

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

        return $weborders;
    }

    public function findByCustomer($customerNumber, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' AND customer = '{$customerNumber}' USE-INDEX customer_date_d", $this->erpOrderSelect, $offset, $limit
        );

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

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

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

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
                . "AND ({$customerSelect}) USE-INDEX customer_date_d", $this->erpOrderSelect, $offset, $limit
        );

        $weborders = $this->_getMultipleDbRecordsFromErp($response);

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
        $limit = 1000;

        do {

            $end = $offset + $limit;

            $output->writeln("Processing records {$offset} to {$end}");

            $response = $this->erp->read(
                    "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O'", $this->erpOrderSelect, $offset, $limit
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
