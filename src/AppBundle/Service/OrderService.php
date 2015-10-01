<?php

namespace AppBundle\Service;

use AppBundle\Entity\Credit;
use AppBundle\Entity\CreditItem;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceItem;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItem;
use AppBundle\Entity\Package;
use AppBundle\Entity\Shipment;
use AppBundle\Entity\ShipmentItem;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderService {

    private $_em;
    private $_erp;
    private $_company;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    private function _loadPackageFromErp(Order $order, $item) {

        $rep = $this->_em->getRepository('AppBundle:Package');

        $package = $rep->findOneBy(array('manifestId' => $item->Manifest_id, 'trackingNumber' => $item->tracking_no));

        if ($package === null) {
            $package = new Package();
            $package->setManifestId($item->Manifest_id)
                    ->setOrderNumber($item->order)
                    ->setRecordSequence($item->rec_seq)
                    ->setTrackingNumber($item->tracking_no)
                    ->setPackageCharge($item->pkg_chg)
                    ->setOrder($order);
            $this->_em->persist($package);
        }

        return $package;
    }

    private function _loadCreditItemsFromErp(Credit $credit) {

        $orderNumber = $credit->getOrderNumber();
        $recordSequence = $credit->getRecordSequence();

        $rep = $this->_em->getRepository('AppBundle:CreditItem');

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {

            $itemObj = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq, 'lineNumber' => $item->line));

            if ($itemObj === null) {
                $itemObj = new CreditItem();

                $itemObj->setItemNumber($item->item)
                        ->setLineNumber($item->line)
                        ->setName(implode(" ", $item->descr))
                        ->setOrderedQuantity($item->q_ord)
                        ->setQuantityCredited($item->q_comm)
                        ->setOrderNumber($item->order)
                        ->setRecordSequence($item->rec_seq)
                        ->setCredit($credit);

                $this->_em->persist($itemObj);
            }

            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadCreditFromErp(Order $order, $item) {

        $rep = $this->_em->getRepository('AppBundle:Credit');

        $credit = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq));

        if ($credit === null) {
            $credit = new Credit();

            $credit->setCustomerPO($item->cu_po)
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
                    ->setOrder($order);

            $this->_em->persist($credit);

            $credit->setItems($this->_loadCreditItemsFromErp($credit));
        } elseif ($credit->getOpen()) {
            $credit->setStatus($item->stat);
            $credit->setOpen($item->opn);
            $this->_em->persist($credit);
        }

        return $credit;
    }

    private function _loadShipmentItemsFromErp(Shipment $shipment) {

        $orderNumber = $shipment->getOrderNumber();
        $recordSequence = $shipment->getRecordSequence();

        $rep = $this->_em->getRepository('AppBundle:ShipmentItem');

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {

            $itemObj = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq, 'lineNumber' => $item->line));

            if ($itemObj === null) {
                $itemObj = new ShipmentItem();

                $itemObj->setItemNumber($item->item)
                        ->setLineNumber($item->line)
                        ->setName(implode(" ", $item->descr))
                        ->setOrderedQuantity($item->q_ord)
                        ->setQuantityShipped($item->q_comm)
                        ->setOrderNumber($item->order)
                        ->setRecordSequence($item->rec_seq)
                        ->setShipment($shipment);

                $this->_em->persist($itemObj);
            }

            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadShipmentFromErp(Order $order, $item) {

        $rep = $this->_em->getRepository('AppBundle:Shipment');

        $shipment = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq));

        if ($shipment === null) {
            $shipment = new Shipment();

            $shipment->setCustomerPO($item->cu_po)
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
                    ->setOrder($order);

            $this->_em->persist($shipment);

            $shipment->setItems($this->_loadShipmentItemsFromErp($shipment));
        } elseif ($shipment->getOpen()) {
            $shipment->setStatus($item->stat);
            $shipment->setOpen($item->opn);
            $this->_em->persist($shipment);
        }

        return $shipment;
    }

    private function _loadInvoiceItemsFromErp(Invoice $invoice) {

        $orderNumber = $invoice->getOrderNumber();
        $recordSequence = $invoice->getRecordSequence();

        $rep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {

            $itemObj = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq, 'lineNumber' => $item->line));

            if ($itemObj === null) {
                $itemObj = new InvoiceItem();

                $itemObj->setItemNumber($item->item)
                        ->setLineNumber($item->line)
                        ->setName(implode(" ", $item->descr))
                        ->setQuantityBilled($item->q_itd)
                        ->setOrderedQuantity($item->q_ord)
                        ->setPrice($item->price)
                        ->setOrderNumber($item->order)
                        ->setRecordSequence($item->rec_seq)
                        ->setInvoice($invoice);

                $this->_em->persist($itemObj);
            }

            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadInvoiceFromErp(Order $order, $item) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');

        $invoice = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq));

        if ($invoice === null) {
            $invoice = new Invoice();

            $invoice->setCustomerPO($item->cu_po)
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
                    ->setOrder($order);

            $this->_em->persist($invoice);


            $invoice->setItems($this->_loadInvoiceItemsFromErp($invoice));
        } elseif ($invoice->getOpen()) {
            $invoice->setStatus($item->stat);
            $invoice->setOpen($item->opn);
            $this->_em->persist($invoice);
        }

        return $invoice;
    }

    private function _loadItemsFromErp(Order $order) {

        $orderNumber = $order->getOrderNumber();
        $recordSequence = $order->getRecordSequence();

        $rep = $this->_em->getRepository('AppBundle:OrderItem');

        $items = new ArrayCollection();

        $query = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND order = '{$orderNumber}' AND rec_seq = '{$recordSequence}'";

        $response = $this->_erp->read($query, "*");

        foreach ($response as $item) {
            $itemObj = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq, 'lineNumber' => $item->line));

            if ($itemObj === null) {
                $itemObj = new OrderItem();

                $itemObj->setItemNumber($item->item)
                        ->setLineNumber($item->line)
                        ->setName(implode(" ", $item->descr))
                        ->setOrderedQuantity($item->q_ord)
                        ->setOrderNumber($item->order)
                        ->setRecordSequence($item->rec_seq)
                        ->setOrder($order);

                $this->_em->persist($itemObj);
            }

            $items[] = $itemObj;
        }

        return $items;
    }

    private function _loadFromErp($item) {

        $rep = $this->_em->getRepository('AppBundle:Order');

        $order = $rep->findOneBy(array('orderNumber' => $item->order, 'recordSequence' => $item->rec_seq));

        $now = new DateTime();

        if ($order === null) {
            $order = new Order();
        } elseif (!$order->getOpen() || $order->getUpdatedOn() > $now->sub(new DateInterval('PT15M'))) {
            return $order;
        }
        
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
                ->setExternalOrderNumber($item->ord_ext);

        $this->_em->persist($order);

        $order->setItems($this->_loadItemsFromErp($order));
        $order->setPackages($this->_findPackages($order));
        $order->setShipments($this->_findShipments($order));
        $order->setInvoices($this->_findInvoices($order));
        $order->setCredits($this->_findCredits($order));

        $this->_em->persist($order);
        $this->_em->flush();

        return $order;
    }

    private function _findCredits(Order $order) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'C' AND order = '{$order->getOrderNumber()}'";

        $response = $this->_erp->read($query, "*");

        $credits = new ArrayCollection();

        foreach ($response as $item) {
            $credits[] = $this->_loadCreditFromErp($order, $item);
        }

        return $credits;
    }

    private function _findInvoices(Order $order) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'I' AND order = '{$order->getOrderNumber()}'";

        $response = $this->_erp->read($query, "*");

        $invoices = new ArrayCollection();

        foreach ($response as $item) {
            $invoices[] = $this->_loadInvoiceFromErp($order, $item);
        }

        return $invoices;
    }

    private function _findShipments(Order $order) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'S' AND order = '{$order->getOrderNumber()}'";

        $response = $this->_erp->read($query, "*");

        $shipments = new ArrayCollection();

        foreach ($response as $item) {
            $shipments[] = $this->_loadShipmentFromErp($order, $item);
        }

        return $shipments;
    }

    private function _findPackages(Order $order) {

        $response = $this->_erp->read(
                "FOR EACH oe_ship_pack NO-LOCK "
                . "WHERE company_oe = '{$this->_company}' "
                . "AND rec_type = 'S' "
                . "AND order = '{$order->getOrderNumber()}' "
                . "AND NOT ( tracking_no BEGINS 'Verify' ) ", "*"
        );

        $packages = new ArrayCollection();

        if (sizeof($response) == 0) {
            return $packages;
        }

        foreach ($response as $item) {
            $packages[] = $this->_loadPackageFromErp($order, $item);
        }

        return $packages;
    }

    public function findBySearchOptions(OrderSearchOptions $searchOptions, $offset, $limit) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O'";

        if ($searchOptions->getOpen() !== null) {
            if ($searchOptions->getOpen() === false) {
                $query .= " AND opn = no";
            } else {
                $query .= " AND opn = yes";
            }
        }

        if ($searchOptions->getCustomerNumber() !== null) {
            $customerNumber = $searchOptions->getCustomerNumber();
            if (is_array($customerNumber)) {
                $customerNumberWhere = " AND (";
                for ($i = 0; $i < sizeof($customerNumber); $i++) {
                    $customerNumberWhere .= " customer = '{$customerNumber[$i]}' ";
                    if ($i < (sizeof($customerNumber) - 1)) {
                        $customerNumberWhere .= " OR ";
                    }
                }
                $customerNumberWhere .= ") ";
            } else {
                $customerNumberWhere = " AND customer = '{$customerNumber}' ";
            }
            $query .= $customerNumberWhere;
        }

        if ($searchOptions->getSearchTerms() !== null) {
            $query .= " AND sy_lookup MATCHES '*{$searchOptions->getSearchTerms()}*'";
        }

        $query .= " USE-INDEX order_d";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        $this->_em->commit();

        return $orders;
    }

    public function findAll($offset, $limit) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' USE-INDEX order_d";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function findBySearchTerms($searchTerms, $offset, $limit) {

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND sy_lookup MATCHES '*{$searchTerms}*' USE-INDEX order_d";

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

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND {$customerNumberWhere} USE-INDEX order_d";

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

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND sy_lookup MATCHES '*{$searchTerms}*' AND {$customerNumberWhere} USE-INDEX order_d";

        $response = $this->_erp->read($query, "*", $offset, $limit);

        $orders = array();

        foreach ($response as $item) {
            $orders[] = $this->_loadFromErp($item);
        }

        return $orders;
    }

    public function find($orderNumber) {

        $rep = $this->_em->getRepository('AppBundle:Order');

        $order = $rep->findOneBy(array('orderNumber' => $orderNumber));

        $now = new DateTime();

        if ($order === null || $order->getUpdatedOn() < $now->sub(new DateInterval('PT5M'))) {

            $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND order = '{$orderNumber}' USE-INDEX order_d";

            $response = $this->_erp->read($query, "*");

            if (sizeof($response) > 0) {
                return $this->_loadFromErp($response[0]);
            }
        } else {

            return $order;
        }

        return null;
    }

    public function refreshOrders(OutputInterface $output) {

        $rep = $this->_em->getRepository('AppBundle:Order');

        $output->writeln("Loading new records...");

        $latestOrder = $rep->findOneBy(array(), array('orderNumber' => 'DESC'));

        if ($latestOrder === null) {
            $output->writeln("There must be at least one record loaded into the database before this task can be run");
        }

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND order > '{$latestOrder->getOrderNumber()}'";

        $response = $this->_erp->read($query, "*");
        
        $this->_em->beginTransaction();
        
        foreach ($response as $item) {
            $output->writeln("Loading: {$item->order}");
            $this->_loadFromErp($item);
        }
        
        $this->_em->commit();

        $output->writeln("New records loaded");
        
    }

}