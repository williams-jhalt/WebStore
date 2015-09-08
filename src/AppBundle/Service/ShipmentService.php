<?php

namespace AppBundle\Service;

use AppBundle\Entity\Shipment;
use AppBundle\Entity\ShipmentItem;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ShipmentService {

    private $_erp;
    private $_company;

    public function __construct(ErpOneConnectorService $erp, $company) {
        $this->_erp = $erp;
        $this->_company = $company;
    }

    private function _loadItemsFromErp($orderNumber, $recordSequence) {

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {
            $itemObj = new ShipmentItem();
            $itemObj->setItemNumber($item->item)
                    ->setLineNumber($item->line)
                    ->setOrderedQuantity($item->q_ord);
            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadFromErp($item) {

        $order = new Shipment();
        $order->setCustomerPO($item->cu_po)
                ->setOpen($item->opn)
                ->setOrderDate(new DateTime($item->ord_date))
                ->setOrderGrossAmount($item->o_tot_gross)
                ->setOrderNumber($item->order)
                ->setRecordSequence($item->rec_seq)
                ->setShipToAddress1($item->adr[0])
                ->setShipToAddress2($item->adr[1])
                ->setShipToAddress3($item->adr[2])
                ->setShipToCity($item->adr[3])
                ->setShipToCountryCode($item->country_code)
                ->setShipToPostalCode($item->postal_code)
                ->setShipToName($item->name)
                ->setCustomerNumber($item->customer)
                ->setStatus($item->stat)
                ->setManifestId($item->Manifest_id)
                ->setItems($this->_loadItemsFromErp($item->order, $item->rec_seq));

        return $order;
    }

    public function findAll($offset, $limit) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S'";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function findBySearchTerms($searchTerms, $offset, $limit) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND sy_lookup MATCHES '*{$searchTerms}*'";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function findByCustomerNumber($customerNumber, $offset, $limit) {

        if (is_array($customerNumber)) {
            $customerNumberWhere = " (";
            for ($i = 0; $i < length($customerNumber); $i++) {
                $customerNumberWhere .= " customer = '{$customerNumber[$i]}' ";
                if ($i < (length($customerNumber) - 1)) {
                    $customerNumberWhere .= " OR ";
                }
            }
            $customerNumberWhere .= ") ";
        } else {
            $customerNumberWhere = " customer = '{$customerNumber}' ";
        }

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND {$customerNumberWhere}";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function findByCustomerNumberAndSearchTerms($customerNumber, $searchTerms, $offset, $limit) {

        if (is_array($customerNumber)) {
            $customerNumberWhere = " (";
            for ($i = 0; $i < length($customerNumber); $i++) {
                $customerNumberWhere .= " customer = '{$customerNumber[$i]}' ";
                if ($i < (length($customerNumber) - 1)) {
                    $customerNumberWhere .= " OR ";
                }
            }
            $customerNumberWhere .= ") ";
        } else {
            $customerNumberWhere = " customer = '{$customerNumber}' ";
        }

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND sy_lookup MATCHES '*{$searchTerms}*' AND {$customerNumberWhere}";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function find($orderNumber) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND order = '{$orderNumber}'";

        $response = $this->_erp->read($query, "*");

        if (sizeof($response) > 0) {
            return $this->_loadFromErp($response[0]);
        }

        return null;
    }

}
