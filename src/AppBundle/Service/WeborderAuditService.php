<?php

namespace AppBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManager;

class WeborderAuditService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    private function _getDbRecordFromErp($item) {
        
        $timeStr = str_pad($item->stat_ttime, 6, "0", STR_PAD_LEFT);
        $dateStr = $item->stat_date;
        $timestamp = DateTime::createFromFormat("Y-m-d His", "{$dateStr} {$timeStr}");        
        
        $data = array(
            'orderNumber' => $item->order,
            'comment' => $item->comment,
            'recordType' => $item->rec_type,
            'statusCode' => $item->stat,
            'timestamp' => $timestamp
        );
        $repository = $this->em->getRepository('AppBundle:WeborderAudit');
        return $repository->findOrCreate($data);
    }

    public function findAll($offset = 0, $limit = 100) {


        $response = $this->erp->read(
                "FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC'", "*", $offset, $limit
        );

        $weborders = array();

        $this->em->beginTransaction();

        foreach ($response as $item) {
            $weborders[] = $this->_getDbRecordFromErp($item);
        }

        $this->em->commit();

        return $weborders;
    }

    public function findByOrderNumber($orderNumber, $offset = 0, $limit = 100) {


        $response = $this->erp->read(
                "FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC' AND order = '{$orderNumber}'", "*", $offset, $limit
        );

        $audits = array();

        $this->em->beginTransaction();

        foreach ($response as $item) {
            $audits[] = $this->_getDbRecordFromErp($item);
        }

        $this->em->commit();

        return $audits;
    }
    
    

}
