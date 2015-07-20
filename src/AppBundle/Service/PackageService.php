<?php

namespace AppBundle\Service;

use AppBundle\Entity\Package;
use DateTime;
use Doctrine\ORM\EntityManager;

class PackageService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    private function _getDbRecordFromErp($item) {
        $repository = $this->em->getRepository('AppBundle:Package');
        return $repository->findOrUpdate(array(
                    'orderNumber' => $item->order,
                    'trackingNumber' => $item->tracking_no,
                    'packageCharge' => $item->pkg_chg
        ));
    }

    public function findAll($offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_ship_pack NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'S' "
                . "AND NOT ( tracking_no BEGINS 'Verify' ) "
                . "BY oe_ship_pack.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();

        $this->em->beginTransaction();

        foreach ($response as $item) {
            $weborder = $this->_getDbRecordFromErp($item);
            $weborders[] = $weborder;
        }

        $this->em->commit();

        return $weborders;
    }

    public function findByOrderNumber($orderNumber, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_ship_pack NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'S' "
                . "AND order= '{$orderNumber}' "
                . "AND NOT ( tracking_no BEGINS 'Verify' ) "
                . "BY oe_ship_pack.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();

        if (sizeof($response) == 0) {
            return $weborders;
        }

        $this->em->beginTransaction();

        foreach ($response as $item) {
            $weborder = $this->_getDbRecordFromErp($item);
            if (!in_array($weborder, $weborders)) {                
                $weborders[] = $weborder;
            }
        }

        $this->em->commit();

        return $weborders;
    }

}
