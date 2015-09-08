<?php

namespace AppBundle\Service;

use AppBundle\Entity\Package;

class PackageService {

    private $_erp;
    private $_company;

    public function __construct(ErpOneConnectorService $erp, $company) {
        $this->_erp = $erp;
        $this->_company = $company;
    }

    private function _loadFromErp($item) {
        $package = new Package();
        $package->setManifestId($item->Manifest_id)
                ->setOrderNumber($item->order)
                ->setTrackingNumber($item->tracking_no)
                ->setPackageCharge($item->pkg_chg);
        return $package;
    }

    public function findAll($offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_ship_pack NO-LOCK "
                . "WHERE company_oe = '{$this->_company}' "
                . "AND rec_type = 'S' "
                . "AND NOT ( tracking_no BEGINS 'Verify' ) "
                . "BY oe_ship_pack.order DESCENDING", "*", $offset, $limit
        );

        $packages = array();

        foreach ($response as $item) {
            $packages[] = $this->_loadFromErp($item);
        }

        return $packages;
    }

    public function findByOrderNumber($orderNumber, $offset = 0, $limit = 100) {

        $response = $this->_erp->read(
                "FOR EACH oe_ship_pack NO-LOCK "
                . "WHERE company_oe = '{$this->_company}' "
                . "AND rec_type = 'S' "
                . "AND order= '{$orderNumber}' "
                . "AND NOT ( tracking_no BEGINS 'Verify' ) "
                . "BY oe_ship_pack.order DESCENDING", "*", $offset, $limit
        );

        $packages = array();

        if (sizeof($response) == 0) {
            return $packages;
        }

        foreach ($response as $item) {
            $packages[] = $this->_loadFromErp($item);
        }

        return $packages;
    }

}
