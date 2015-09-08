<?php

namespace AppBundle\Service;

use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceItem;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class InvoiceService {

    private $_erp;
    private $_company;
    private $_cache;

    public function __construct(ErpOneConnectorService $erp, $company, $cache) {
        $this->_erp = $erp;
        $this->_company = $company;
        $this->_cache = $cache;
    }

    private function _loadItemsFromErp($orderNumber, $recordSequence) {

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {
            $itemObj = new InvoiceItem();
            $itemObj->setItemNumber($item->item)
                    ->setLineNumber($item->line)
                    ->setOrderedQuantity($item->q_ord);
            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadFromErp($item) {
        
        $order = new Invoice();
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
                ->setInvoiceDate(new DateTime($item->invc_date))
                ->setInvoiceNumber($item->invoice)
                ->setCustomerNumber($item->customer)
                ->setStatus($item->stat)
                ->setItems($this->_loadItemsFromErp($item->order, $item->rec_seq));

        return $order;
    }

    public function findAll($offset, $limit) {

        $key = md5("AppBundle:InvoiceService:findAll:{$offset}:{$limit}");

        if (($orders = $this->_cache->fetch($key)) === false) {

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I'";

            $response = $this->_erp->read($query, "*", $offset, $limit);

            $orders = array();

            foreach ($response as $item) {
                $orders[] = $this->_loadFromErp($item);
            }

            $this->_cache->save($key, $orders, 300);
        }

        return $orders;
    }

    public function findBySearchTerms($searchTerms, $offset, $limit) {

        $key = md5("AppBundle:InvoiceService:findBySearchTerms:{$searchTerms}:{$offset}:{$limit}");

        if (($orders = $this->_cache->fetch($key)) === false) {

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND sy_lookup MATCHES '*{$searchTerms}*'";

            $response = $this->_erp->read($query, "*", $offset, $limit);

            $orders = array();

            foreach ($response as $item) {
                $orders[] = $this->_loadFromErp($item);
            }

            $this->_cache->save($key, $orders, 300);
        }

        return $orders;
    }

    public function findByCustomerNumber($customerNumber, $offset, $limit) {

        $key = md5("AppBundle:InvoiceService:findByCustomerNumber:" . serialize($customerNumber) . ":{$offset}:{$limit}");

        if (($orders = $this->_cache->fetch($key)) === false) {

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

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND {$customerNumberWhere}";

            $response = $this->_erp->read($query, "*", $offset, $limit);

            $orders = array();

            foreach ($response as $item) {
                $orders[] = $this->_loadFromErp($item);
            }

            $this->_cache->save($key, $orders, 300);
        }

        return $orders;
    }

    public function findByCustomerNumberAndSearchTerms($customerNumber, $searchTerms, $offset, $limit) {

        $key = md5("AppBundle:InvoiceService:findByCustomerNumberAndSearchTerms:" . serialize($customerNumber) . ":{$searchTerms}:{$offset}:{$limit}");

        if (($orders = $this->_cache->fetch($key)) === false) {

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

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND sy_lookup MATCHES '*{$searchTerms}*' AND {$customerNumberWhere}";

            $response = $this->_erp->read($query, "*", $offset, $limit);

            $orders = array();

            foreach ($response as $item) {
                $orders[] = $this->_loadFromErp($item);
            }

            $this->_cache->save($key, $orders, 300);
        }

        return $orders;
    }

    public function find($orderNumber) {

        $key = md5("AppBundle:InvoiceService:find:{$orderNumber}");

        if (($orders = $this->_cache->fetch($key)) === false) {

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND order = '{$orderNumber}'";

            $response = $this->_erp->read($query, "*");

            if (sizeof($response) > 0) {
                $order = $this->_loadFromErp($response[0]);
            }

            $this->_cache->save($key, $order, 300);
        }

        return $order;
    }

}
