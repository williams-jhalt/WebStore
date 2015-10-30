<?php

namespace AppBundle\Service;

use AppBundle\Entity\Credit;
use AppBundle\Entity\CreditItem;
use AppBundle\Entity\ErpItem;
use AppBundle\Entity\ErpOrder;
use AppBundle\Entity\ErpPackage;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceItem;
use AppBundle\Entity\Package;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\SalesOrderItem;
use AppBundle\Entity\Shipment;
use AppBundle\Entity\ShipmentItem;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOrderSyncService {

    /**
     *
     * @var EntityManager
     */
    private $_em;

    /**
     *
     * @var ErpOneConnectorService
     */
    private $_erp;

    /**
     *
     * @var string
     */
    private $_company;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    public function findCombinedInvoices($params, $offset = 0, $limit = 100) {
        
    }

    public function updateOrder(SalesOrder $so) {

        $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order = {$so->getOrderNumber()}";
        $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order = {$so->getOrderNumber()}";
        $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order = {$so->getOrderNumber()}";

        $this->_readHeaderFromErp($headerQuery);
        $this->_readDetailFromErp($detailQuery);
        $this->_readPackageFromErp($packageQuery);

        $this->_updateSalesOrder($so);
    }

    public function loadFromErp(OutputInterface $output) {

        $rep = $this->_em->getRepository('AppBundle:ErpOrder');


//        $openOrders = $rep->findBy(array('open' => true), array('orderNumber' => 'ASC'));
//
//        $groups = array();
//        $first = $openOrders[0]->getOrderNumber();
//        $last = $openOrders[0]->getOrderNumber();
//
//        foreach ($openOrders as $o) {
//            if ($o->getOrderNumber() > $last + 1) {
//                $groups[] = array($first, $last);
//                $first = $o->getOrderNumber();
//            }
//            $last = $o->getOrderNumber();
//        }
//
//        if (empty($openOrders)) {
//            // if this is a new database, only get the last 5 days of open orders
//            $firstRecentOpenOrderRes = $this->_erp->read("FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND opn = yes AND order > 0 AND INTERVAL(NOW, ord_date, 'days') < 5", "order", 0, 1);
//            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
//            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
//            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
//
//            $this->_readHeaderFromErp($headerQuery);
//            $this->_readDetailFromErp($detailQuery);
//            $this->_readPackageFromErp($packageQuery);
//        } else {
//
//            foreach ($groups as $group) {
//
//                $this->_output->writeln("Reading orders {$group[0]} to {$group[1]}");
//
//                $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$group[0]} AND order <= {$group[1]}";
//                $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$group[0]} AND order <= {$group[1]}";
//                $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$group[0]} AND order <= {$group[1]}";
//
//                $this->_readHeaderFromErp($headerQuery);
//                $this->_readDetailFromErp($detailQuery);
//                $this->_readPackageFromErp($packageQuery);
//            }
//
//            $this->_output->writeln("Reading new orders after {$last}");
//
//            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$last}";
//            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$last}";
//            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$last}";
//
//            $this->_readHeaderFromErp($headerQuery);
//            $this->_readDetailFromErp($detailQuery);
//            $this->_readPackageFromErp($packageQuery);
//        }

        $firstOpenOrder = $rep->findOneBy(array('consolidated' => false, 'open' => true), array('orderNumber' => 'ASC'));

        if ($firstOpenOrder === null) {

            // if this is a new database, only get the last 5 days of open orders
            $firstRecentOrder = $this->_erp->read("FOR EACH oe_head NO-LOCK WHERE oe_head.company_oe = '{$this->_company}' AND INTERVAL(NOW, oe_head.ord_date, 'days') <= 1 USE-INDEX order_d", "oe_head.order", 0, 1);

            $firstOrderNumber = abs($firstRecentOrder[0]->oe_head_order);

            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE oe_head.company_oe = '{$this->_company}' AND ABS(oe_head.order) >= {$firstOrderNumber}";
            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE oe_line.company_oe = '{$this->_company}' AND oe_line.item <> '' AND ABS(oe_line.order) >= {$firstOrderNumber}";
            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE oe_ship_pack.company_oe = '{$this->_company}' AND ABS(oe_ship_pack.order) >= {$firstOrderNumber}";
        } else {
            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE oe_head.company_oe = '{$this->_company}' AND oe_head.order >= {$firstOpenOrder->getOrderNumber()}";
            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE oe_line.company_oe = '{$this->_company}' AND oe_line.item <> '' AND oe_line.order >= {$firstOpenOrder->getOrderNumber()}";
            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE oe_ship_pack.company_oe = '{$this->_company}' AND oe_ship_pack.order >= {$firstOpenOrder->getOrderNumber()}";
        }

        $this->_readHeaderFromErp($headerQuery);
        $this->_readDetailFromErp($detailQuery);
        $this->_readPackageFromErp($packageQuery);

        $lastConsolidatedInvoice = $rep->findOneBy(array('consolidated' => true), array('orderNumber' => 'DESC'));

        if ($lastConsolidatedInvoice !== null) {

            $lastConsolidatedInvoiceNumber = abs($lastConsolidatedInvoice->getOrderNumber());

            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE oe_head.company_oe = '{$this->_company}' AND oe_head.consolidated_order = yes AND ABS(oe_head.order) > {$lastConsolidatedInvoiceNumber}";
            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE oe_line.company_oe = '{$this->_company}' AND oe_line.item <> '' AND ABS(oe_line.order) > {$lastConsolidatedInvoiceNumber}, EACH oe_head NO-LOCK WHERE oe_head.order = oe_line.order AND oe_head.rec_type = oe_line.rec_type AND oe_head.rec_seq = oe_line.rec_seq AND oe_head.company_oe = oe_line.company_oe AND oe_head.consolidated_order = yes";

            $this->_readHeaderFromErp($headerQuery);
            $this->_readDetailFromErp($detailQuery);
        }

        $this->_generateSalesOrders();
    }

    private function _readHeaderFromErp($query) {

        $fields = "oe_head.order,"
                . "oe_head.rec_seq,"
                . "oe_head.rec_type,"
                . "oe_head.name,"
                . "oe_head.adr,"
                . "oe_head.state,"
                . "oe_head.postal_code,"
                . "oe_head.country_code,"
                . "oe_head.ship_via_code,"
                . "oe_head.cu_po,"
                . "oe_head.ord_date,"
                . "oe_head.opn,"
                . "oe_head.o_tot_gross,"
                . "oe_head.stat,"
                . "oe_head.customer,"
                . "oe_head.ord_ext,"
                . "oe_head.invoice,"
                . "oe_head.c_tot_code_amt,"
                . "oe_head.c_tot_gross,"
                . "oe_head.c_tot_net_ar,"
                . "oe_head.invc_date,"
                . "oe_head.Manifest_id,"
                . "oe_head.ship_date,"
                . "oe_head.consolidated_order";

        $offset = 0;
        $limit = 5000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);


            foreach ($response as $row) {
                $this->_loadHeaderRecord($row);
            }

            $this->_em->flush();

            $offset = $offset + $limit;
        } while (!empty($response));
    }

    private function _readDetailFromErp($query) {

        $fields = "oe_line.order,"
                . "oe_line.rec_seq,"
                . "oe_line.line,"
                . "oe_line.rec_type,"
                . "oe_line.item,"
                . "oe_line.descr,"
                . "oe_line.price,"
                . "oe_line.q_ord,"
                . "oe_line.q_itd,"
                . "oe_line.q_comm";

        $offset = 0;
        $limit = 5000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);

            foreach ($response as $row) {
                $this->_loadDetailRecord($row);
            }

            $this->_em->flush();

            $offset = $offset + $limit;
        } while (!empty($response));
    }

    private function _readPackageFromErp($query) {

        $fields = "oe_ship_pack.order,"
                . "oe_ship_pack.rec_seq,"
                . "oe_ship_pack.tracking_no,"
                . "oe_ship_pack.Manifest_id,"
                . "oe_ship_pack.ship_via_code,"
                . "oe_ship_pack.pkg_chg,"
                . "oe_ship_pack.pack_weight,"
                . "oe_ship_pack.pack_height,"
                . "oe_ship_pack.pack_length,"
                . "oe_ship_pack.pack_width";

        $offset = 0;
        $limit = 1000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);

            foreach ($response as $row) {
                $this->_loadPackageRecord($row);
            }

            $this->_em->flush();

            $offset = $offset + $limit;
        } while (!empty($response));
    }

    private function _loadHeaderRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpOrder');

        $order = $rep->find(array('orderNumber' => $row->oe_head_order, 'recordSequence' => $row->oe_head_rec_seq, 'recordType' => $row->oe_head_rec_type));

        if ($order === null) {
            $order = new ErpOrder($row->oe_head_order, $row->oe_head_rec_seq, $row->oe_head_rec_type);
        } elseif (!$order->getOpen() || $row->oe_head_opn) {
            return;
        }

        $order->setShipToName($row->oe_head_name)
                ->setShipToAddress1($row->oe_head_adr[0])
                ->setShipToAddress2($row->oe_head_adr[1])
                ->setShipToAddress3($row->oe_head_adr[2])
                ->setShipToCity($row->oe_head_adr[3])
                ->setShipToState($row->oe_head_state)
                ->setShipToPostalCode($row->oe_head_postal_code)
                ->setShipToCountryCode($row->oe_head_country_code)
                ->setShipViaCode($row->oe_head_ship_via_code)
                ->setCustomerPO($row->oe_head_cu_po)
                ->setOrderDate(new DateTime($row->oe_head_ord_date))
                ->setOpen($row->oe_head_opn)
                ->setOrderGrossAmount($row->oe_head_o_tot_gross)
                ->setStatus($row->oe_head_stat)
                ->setCustomerNumber($row->oe_head_customer)
                ->setExternalOrderNumber($row->oe_head_ord_ext)
                ->setInvoiceNumber($row->oe_head_invoice)
                ->setFreightCharge($row->oe_head_c_tot_code_amt[0])
                ->setShippingAndHandlingCharge($row->oe_head_c_tot_code_amt[1])
                ->setInvoiceGrossAmount($row->oe_head_c_tot_gross)
                ->setInvoiceNetAmount($row->oe_head_c_tot_net_ar)
                ->setInvoiceDate(new DateTime($row->oe_head_invc_date))
                ->setManifestId($row->oe_head_Manifest_id)
                ->setShipDate(new DateTime($row->oe_head_ship_date))
                ->setConsolidated($row->oe_head_consolidated_order)
                ->setInvoiceNumber($row->oe_head_invoice);

        $this->_em->persist($order);
    }

    private function _loadDetailRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpItem');

        $item = $rep->find(array('orderNumber' => $row->oe_line_order, 'recordSequence' => $row->oe_line_rec_seq, 'lineNumber' => $row->oe_line_line, 'recordType' => $row->oe_line_rec_type));

        if ($item === null) {
            $item = new ErpItem($row->oe_line_order, $row->oe_line_rec_seq, $row->oe_line_line, $row->oe_line_rec_type);

            $item->setItemNumber($row->oe_line_item)
                    ->setName(implode(" ", $row->oe_line_descr))
                    ->setPrice($row->oe_line_price)
                    ->setQuantityOrdered($row->oe_line_q_ord)
                    ->setQuantityBilled($row->oe_line_q_itd)
                    ->setQuantityShipped($row->oe_line_q_comm);

            $this->_em->persist($item);
        }
    }

    private function _loadPackageRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpPackage');

        $item = $rep->find(array('orderNumber' => $row->oe_ship_pack_order, 'recordSequence' => $row->oe_ship_pack_rec_seq, 'trackingNumber' => $row->oe_ship_pack_tracking_no));

        if ($item === null) {
            $item = new ErpPackage($row->oe_ship_pack_order, $row->oe_ship_pack_rec_seq, $row->oe_ship_pack_tracking_no);
            $item->setManifestId($row->oe_ship_pack_Manifest_id)
                    ->setShipViaCode($row->oe_ship_pack_ship_via_code)
                    ->setPackageCharge($row->oe_ship_pack_pkg_chg)
                    ->setWeight($row->oe_ship_pack_pack_weight)
                    ->setHeight($row->oe_ship_pack_pack_height)
                    ->setLength($row->oe_ship_pack_pack_length)
                    ->setWidth($row->oe_ship_pack_pack_width);

            $this->_em->persist($item);
        }
    }

    private function _generateSalesOrders() {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:SalesOrder');
        $itemRep = $this->_em->getRepository('AppBundle:SalesOrderItem');

        $lastKnownSalesOrder = $rep->findOneBy(array(), array('orderNumber' => 'DESC'));

        if ($lastKnownSalesOrder === null) {

            $erpOrders = $erpRep->findBy(array('recordType' => 'O'));
        } else {

            $erpOrders = $erpRep->createQueryBuilder('e')
                    ->where("e.recordType = 'O'")
                    ->andWhere('e.orderNumber > :orderNumber')
                    ->setParameter('orderNumber', $lastKnownSalesOrder->getOrderNumber())
                    ->getQuery()
                    ->getResult();
        }

        $count = 0;
        $blockSize = 1000;

        $salesOrders = new ArrayCollection();

        foreach ($erpOrders as $t) {

            $so = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

            if ($so === null) {
                $so = new SalesOrder();
            } elseif (!$so->getOpen()) {
                continue;
            }

            $so->setOrderNumber($t->getOrderNumber())
                    ->setRecordSequence($t->getRecordSequence())
                    ->setShipToName($t->getShipToName())
                    ->setShipToAddress1($t->getShipToAddress1())
                    ->setShipToAddress2($t->getShipToAddress2())
                    ->setShipToAddress3($t->getShipToAddress3())
                    ->setShipToCity($t->getShipToCity())
                    ->setShipToState($t->getShipToState())
                    ->setShipToPostalCode($t->getShipToPostalCode())
                    ->setShipToCountryCode($t->getShipToCountryCode())
                    ->setShipViaCode($t->getShipViaCode())
                    ->setCustomerPO($t->getCustomerPO())
                    ->setOrderDate($t->getOrderDate())
                    ->setOpen($t->getOpen())
                    ->setOrderGrossAmount($t->getOrderGrossAmount())
                    ->setStatus($t->getStatus())
                    ->setCustomerNumber($t->getCustomerNumber())
                    ->setExternalOrderNumber($t->getExternalOrderNumber());

            $so->setInvoices($this->_generateInvoices($so));
            $so->setShipments($this->_generateShipments($so));
            $so->setCredits($this->_generateCredits($so));
            $so->setPackages($this->_generatePackages($so));

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'O'));

            $items = new ArrayCollection();

            foreach ($erpItems as $x) {

                $soi = $itemRep->findOneBy(array('salesOrder' => $so, 'lineNumber' => $x->getLineNumber()));

                if ($soi === null) {
                    $soi = new SalesOrderItem();
                }

                $soi->setLineNumber($x->getLineNumber())
                        ->setItemNumber($x->getItemNumber())
                        ->setName($x->getName())
                        ->setPrice($x->getPrice())
                        ->setQuantityOrdered($x->getQuantityOrdered())
                        ->setSalesOrder($so);

                $this->_em->persist($soi);

                $items[] = $soi;
            }

            $so->setItems($items);

            $this->_em->persist($so);

            $salesOrders[] = $so;

            $count++;

            if (($count % $blockSize) == 0) {
                $this->_em->flush();
            }
        }

        $this->_em->flush();
    }

    private function _findParentInvoice($invoiceNumber) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $invoice = $rep->findOneBy(array('invoiceNumber' => $invoiceNumber, 'consolidated' => true));

        if ($invoice !== null) {
            return $invoice;
        }

        $erpInvoice = $erpRep->findOneBy(array('invoiceNumber' => $invoiceNumber, 'consolidated' => true));

        if ($erpInvoice === null) {
            return null;
        }

        $invoice = new Invoice();

        $invoice->setOrderNumber($erpInvoice->getOrderNumber())
                ->setInvoiceNumber($erpInvoice->getInvoiceNumber())
                ->setRecordSequence($erpInvoice->getRecordSequence())
                ->setOpen($erpInvoice->getOpen())
                ->setStatus($erpInvoice->getStatus())
                ->setInvoiceDate($erpInvoice->getInvoiceDate())
                ->setConsolidated($erpInvoice->getConsolidated())
                ->setCustomerNumber($erpInvoice->getCustomerNumber())
                ->setGrossAmount($erpInvoice->getInvoiceGrossAmount())
                ->setNetAmount($erpInvoice->getInvoiceNetAmount())
                ->setFreightCharge($erpInvoice->getFreightCharge())
                ->setShippingAndHandlingCharge($erpInvoice->getShippingAndHandlingCharge());

        $erpItems = $erpItemRep->findBy(array(
            'orderNumber' => $erpInvoice->getOrderNumber(),
            'recordSequence' => $erpInvoice->getRecordSequence(),
            'recordType' => 'I'));

        $items = new ArrayCollection();

        foreach ($erpItems as $erpItem) {

            $soi = $itemRep->findOneBy(array('invoice' => $invoice, 'lineNumber' => $erpItem->getLineNumber()));

            if ($soi === null) {
                $soi = new InvoiceItem();
            }

            $soi->setLineNumber($erpItem->getLineNumber())
                    ->setItemNumber($erpItem->getItemNumber())
                    ->setName($erpItem->getName())
                    ->setPrice($erpItem->getPrice())
                    ->setQuantityOrdered($erpItem->getQuantityOrdered())
                    ->setQuantityBilled($erpItem->getQuantityBilled())
                    ->setQuantityShipped($erpItem->getQuantityShipped())
                    ->setInvoice($invoice);

            $this->_em->persist($soi);

            $items[] = $soi;
        }

        $invoice->setItems($items);

        $this->_em->persist($invoice);
        $this->_em->flush($invoice);

        return $invoice;
    }

    private function _generateInvoices(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'I', 'orderNumber' => $salesOrder->getOrderNumber()));

        $invoices = new ArrayCollection();

        foreach ($erpOrders as $t) {

            $invoice = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

            if ($invoice === null) {
                $invoice = new Invoice();
            } elseif (!$invoice->getOpen()) {
                continue;
            }

            $invoice->setOrderNumber($t->getOrderNumber())
                    ->setInvoiceNumber($t->getInvoiceNumber())
                    ->setRecordSequence($t->getRecordSequence())
                    ->setOpen($t->getOpen())
                    ->setStatus($t->getStatus())
                    ->setSalesOrder($salesOrder)
                    ->setInvoiceDate($t->getInvoiceDate())
                    ->setConsolidated($t->getConsolidated())
                    ->setCustomerNumber($t->getCustomerNumber())
                    ->setGrossAmount($t->getInvoiceGrossAmount())
                    ->setNetAmount($t->getInvoiceNetAmount())
                    ->setFreightCharge($t->getFreightCharge())
                    ->setShippingAndHandlingCharge($t->getShippingAndHandlingCharge());

            $parent = $this->_findParentInvoice($t->getInvoiceNumber());

            if ($parent !== null) {
                $parent->getConsolidatedSalesOrders()->add($salesOrder);
                $invoice->setParent($parent);
            }

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'I'));

            $items = new ArrayCollection();

            foreach ($erpItems as $x) {

                $soi = $itemRep->findOneBy(array('invoice' => $invoice, 'lineNumber' => $x->getLineNumber()));

                if ($soi === null) {
                    $soi = new InvoiceItem();
                }

                $soi->setLineNumber($x->getLineNumber())
                        ->setItemNumber($x->getItemNumber())
                        ->setName($x->getName())
                        ->setPrice($x->getPrice())
                        ->setQuantityOrdered($x->getQuantityOrdered())
                        ->setQuantityBilled($x->getQuantityBilled())
                        ->setQuantityShipped($x->getQuantityShipped())
                        ->setInvoice($invoice);

                $this->_em->persist($soi);

                $items[] = $soi;
            }

            $invoice->setItems($items);

            $this->_em->persist($invoice);

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    private function _generateShipments(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Shipment');
        $itemRep = $this->_em->getRepository('AppBundle:ShipmentItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'S', 'orderNumber' => $salesOrder->getOrderNumber()));

        $shipments = new ArrayCollection();

        foreach ($erpOrders as $t) {

            $shipment = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

            if ($shipment === null) {
                $shipment = new Shipment();
            } elseif (!$shipment->getOpen()) {
                continue;
            }

            $shipment->setOrderNumber($t->getOrderNumber())
                    ->setRecordSequence($t->getRecordSequence())
                    ->setOpen($t->getOpen())
                    ->setStatus($t->getStatus())
                    ->setSalesOrder($salesOrder);

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'S'));

            $items = new ArrayCollection();

            foreach ($erpItems as $x) {

                $soi = $itemRep->findOneBy(array('shipment' => $shipment, 'lineNumber' => $x->getLineNumber()));

                if ($soi === null) {
                    $soi = new ShipmentItem();
                }

                $soi->setLineNumber($x->getLineNumber())
                        ->setItemNumber($x->getItemNumber())
                        ->setName($x->getName())
                        ->setPrice($x->getPrice())
                        ->setQuantityOrdered($x->getQuantityOrdered())
                        ->setQuantityShipped($x->getQuantityShipped())
                        ->setShipment($shipment);

                $this->_em->persist($soi);

                $items[] = $soi;
            }

            $shipment->setItems($items);

            $this->_em->persist($shipment);

            $shipments[] = $shipment;
        }

        return $shipments;
    }

    private function _generateCredits(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Credit');
        $itemRep = $this->_em->getRepository('AppBundle:CreditItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'C', 'orderNumber' => $salesOrder->getOrderNumber()));

        $credits = new ArrayCollection();

        foreach ($erpOrders as $t) {

            $credit = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

            if ($credit === null) {
                $credit = new Credit();
            } elseif (!$credit->getOpen()) {
                continue;
            }

            $credit->setOrderNumber($t->getOrderNumber())
                    ->setRecordSequence($t->getRecordSequence())
                    ->setOpen($t->getOpen())
                    ->setStatus($t->getStatus())
                    ->setSalesOrder($salesOrder);

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'C'));

            $items = new ArrayCollection();

            foreach ($erpItems as $x) {

                $soi = $itemRep->findOneBy(array('credit' => $credit, 'lineNumber' => $x->getLineNumber()));

                if ($soi === null) {
                    $soi = new CreditItem();
                }

                $soi->setLineNumber($x->getLineNumber())
                        ->setItemNumber($x->getItemNumber())
                        ->setName($x->getName())
                        ->setPrice($x->getPrice())
                        ->setQuantityOrdered($x->getQuantityOrdered())
                        ->setQuantityCredited($x->getQuantityBilled())
                        ->setCredit($credit);

                $this->_em->persist($soi);

                $items[] = $soi;
            }

            $credit->setItems($items);

            $this->_em->persist($credit);

            $credits[] = $credit;
        }

        return $credits;
    }

    private function _generatePackages(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpPackage');
        $rep = $this->_em->getRepository('AppBundle:Package');

        $erpOrders = $erpRep->findBy(array('orderNumber' => $salesOrder->getOrderNumber()));

        $packages = new ArrayCollection();

        foreach ($erpOrders as $t) {

            if (!preg_match("/Verify Box.*/", $t->getTrackingNumber())) {

                $package = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

                if ($package === null) {
                    $package = new Package();
                }

                $package->setOrderNumber($t->getOrderNumber())
                        ->setRecordSequence($t->getRecordSequence())
                        ->setTrackingNumber($t->getTrackingNumber())
                        ->setManifestId($t->getManifestId())
                        ->setShipViaCode($t->getShipViaCode())
                        ->setPackageCharge($t->getPackageCharge())
                        ->setWeight($t->getWeight())
                        ->setHeight($t->getHeight())
                        ->setLength($t->getLength())
                        ->setWidth($t->getWidth())
                        ->setSalesOrder($salesOrder);

                $this->_em->persist($package);

                $packages[] = $package;
            }
        }

        return $packages;
    }

    private function _updateSalesOrder(SalesOrder $so) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $itemRep = $this->_em->getRepository('AppBundle:SalesOrderItem');

        $erpOrder = $erpRep->createQueryBuilder('e')
                ->where("e.recordType = 'O'")
                ->andWhere('e.orderNumber = :orderNumber')
                ->andWhere('e.recordSequence = :recordSequence')
                ->setParameter('orderNumber', $so->getOrderNumber())
                ->setParameter('recordSequence', $so->getRecordSequence())
                ->getQuery()
                ->getOneOrNullResult();

        if ($erpOrder === null) {
            return;
        }

        $so->setShipToName($erpOrder->getShipToName())
                ->setShipToAddress1($erpOrder->getShipToAddress1())
                ->setShipToAddress2($erpOrder->getShipToAddress2())
                ->setShipToAddress3($erpOrder->getShipToAddress3())
                ->setShipToCity($erpOrder->getShipToCity())
                ->setShipToState($erpOrder->getShipToState())
                ->setShipToPostalCode($erpOrder->getShipToPostalCode())
                ->setShipToCountryCode($erpOrder->getShipToCountryCode())
                ->setShipViaCode($erpOrder->getShipViaCode())
                ->setCustomerPO($erpOrder->getCustomerPO())
                ->setOrderDate($erpOrder->getOrderDate())
                ->setOpen($erpOrder->getOpen())
                ->setOrderGrossAmount($erpOrder->getOrderGrossAmount())
                ->setStatus($erpOrder->getStatus())
                ->setCustomerNumber($erpOrder->getCustomerNumber())
                ->setExternalOrderNumber($erpOrder->getExternalOrderNumber());

        $this->_em->persist($so);

        $so->setInvoices($this->_generateInvoices($so));
        $so->setShipments($this->_generateShipments($so));
        $so->setCredits($this->_generateCredits($so));
        $so->setPackages($this->_generatePackages($so));

        $erpItems = $erpItemRep->findBy(array(
            'orderNumber' => $erpOrder->getOrderNumber(),
            'recordSequence' => $erpOrder->getRecordSequence(),
            'recordType' => 'O'));

        $items = new ArrayCollection();

        foreach ($erpItems as $x) {

            $soi = $itemRep->findOneBy(array('salesOrder' => $so, 'lineNumber' => $x->getLineNumber()));

            if ($soi === null) {
                $soi = new SalesOrderItem();
            }

            $soi->setLineNumber($x->getLineNumber())
                    ->setItemNumber($x->getItemNumber())
                    ->setName($x->getName())
                    ->setPrice($x->getPrice())
                    ->setQuantityOrdered($x->getQuantityOrdered())
                    ->setSalesOrder($so);

            $this->_em->persist($soi);

            $items[] = $soi;
        }

        $so->setItems($items);

        $this->_em->persist($so);

        $this->_em->flush();
    }

}
