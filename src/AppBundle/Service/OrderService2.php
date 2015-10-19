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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

class OrderService2 {

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

    /**
     *
     * @var OutputInterface
     */
    private $_output;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    public function loadFromErp(OutputInterface $output) {

        $rep = $this->_em->getRepository('AppBundle:ErpOrder');

        $firstOpenOrder = $rep->findOneBy(array('open' => true), array('orderNumber' => 'ASC'));

        if ($firstOpenOrder === null) {

            // if this is a new database, only get the last 5 days of open orders
            $firstRecentOpenOrderRes = $this->_erp->read("FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND opn = yes AND order > 0 AND INTERVAL(NOW, ord_date, 'days') < 5", "order", 0, 1);

            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order >= {$firstRecentOpenOrderRes[0]->order}";
        } else {
            $headerQuery = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND order > {$firstOpenOrder->getOrderNumber()}";
            $detailQuery = "FOR EACH oe_line NO-LOCK WHERE company_oe = '{$this->_company}' AND order > {$firstOpenOrder->getOrderNumber()}";
            $packageQuery = "FOR EACH oe_ship_pack NO-LOCK WHERE company_oe = '{$this->_company}' AND order > {$firstOpenOrder->getOrderNumber()}";
        }

        $this->_output = $output;

        $this->_readHeaderFromErp($headerQuery);
        $this->_readDetailFromErp($detailQuery);
        $this->_readPackageFromErp($packageQuery);

        $this->_generateSalesOrders();
    }

    private function _readHeaderFromErp($query) {

        $this->_output->writeln("Loading header information...");

        $fields = "order,rec_seq,rec_type,name,adr,state,postal_code,country_code,ship_via_code,cu_po,ord_date,opn,o_tot_gross,stat,customer,ord_ext,invoice,c_tot_code_amt,c_tot_gross,c_tot_net_ar,invc_date,Manifest_id,ship_date";

        $offset = 0;
        $limit = 5000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);


            foreach ($response as $row) {
                $this->_loadHeaderRecord($row);
                $this->_output->write(".");
            }

            $this->_em->flush();

            $offset = $offset + $limit;

            $this->_output->writeln("Loaded {$offset} items");
        } while (!empty($response));

        $this->_output->writeln("\nHeader information loaded!");
    }

    private function _readDetailFromErp($query) {

        $this->_output->writeln("Loading detail information...");

        $fields = "order,rec_seq,line,rec_type,item,descr,price,q_ord,q_itd,q_comm";

        $offset = 0;
        $limit = 5000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);

            foreach ($response as $row) {
                $this->_loadDetailRecord($row);
                $this->_output->write(".");
            }

            $this->_em->flush();

            $offset = $offset + $limit;

            $this->_output->writeln("Loaded {$offset} items");
        } while (!empty($response));


        $this->_output->writeln("\nDetail information loaded!");
    }

    private function _readPackageFromErp($query) {

        $this->_output->writeln("Loading package information...");

        $fields = "order,rec_seq,tracking_no,Manifest_id,ship_via_code,pkg_chg,pack_weight,pack_height,pack_length,pack_width";

        $offset = 0;
        $limit = 1000;

        do {

            $response = $this->_erp->read($query, $fields, $offset, $limit);

            foreach ($response as $row) {
                $this->_loadPackageRecord($row);
                $this->_output->write(".");
            }

            $this->_em->flush();

            $offset = $offset + $limit;
        } while (!empty($response));


        $this->_output->writeln("\nDetail information loaded!");
    }

    private function _loadHeaderRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpOrder');

        $order = $rep->find(array('orderNumber' => $row->order, 'recordSequence' => $row->rec_seq, 'recordType' => $row->rec_type));

        if ($order === null) {
            $order = new ErpOrder($row->order, $row->rec_seq, $row->rec_type);
        }

        $order->setShipToName($row->name)
                ->setShipToAddress1($row->adr[0])
                ->setShipToAddress2($row->adr[1])
                ->setShipToAddress3($row->adr[2])
                ->setShipToCity($row->adr[3])
                ->setShipToState($row->state)
                ->setShipToPostalCode($row->postal_code)
                ->setShipToCountryCode($row->country_code)
                ->setShipViaCode($row->ship_via_code)
                ->setCustomerPO($row->cu_po)
                ->setOrderDate(new DateTime($row->ord_date))
                ->setOpen($row->opn)
                ->setOrderGrossAmount($row->o_tot_gross)
                ->setStatus($row->stat)
                ->setCustomerNumber($row->customer)
                ->setExternalOrderNumber($row->ord_ext)
                ->setInvoiceNumber($row->invoice)
                ->setFreightCharge($row->c_tot_code_amt[0])
                ->setShippingAndHandlingCharge($row->c_tot_code_amt[1])
                ->setInvoiceGrossAmount($row->c_tot_gross)
                ->setInvoiceNetAmount($row->c_tot_net_ar)
                ->setInvoiceDate(new DateTime($row->invc_date))
                ->setManifestId($row->Manifest_id)
                ->setShipDate(new DateTime($row->ship_date));

        $this->_em->persist($order);
    }

    private function _loadDetailRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpItem');

        $item = $rep->find(array('orderNumber' => $row->order, 'recordSequence' => $row->rec_seq, 'lineNumber' => $row->line, 'recordType' => $row->rec_type));

        if ($item === null) {
            $item = new ErpItem($row->order, $row->rec_seq, $row->line, $row->rec_type);
        }

        $item->setItemNumber($row->item)
                ->setName(implode(" ", $row->descr))
                ->setPrice($row->price)
                ->setQuantityOrdered($row->q_ord)
                ->setQuantityBilled($row->q_itd)
                ->setQuantityShipped($row->q_comm);

        $this->_em->persist($item);
    }

    private function _loadPackageRecord($row) {

        $rep = $this->_em->getRepository('AppBundle:ErpPackage');

        $item = $rep->find(array('orderNumber' => $row->order, 'recordSequence' => $row->rec_seq, 'trackingNumber' => $row->tracking_no));

        if ($item === null) {
            $item = new ErpPackage($row->order, $row->rec_seq, $row->tracking_no);
        }

        $item->setManifestId($row->Manifest_id)
                ->setShipViaCode($row->ship_via_code)
                ->setPackageCharge($row->pkg_chg)
                ->setWeight($row->pack_weight)
                ->setHeight($row->pack_height)
                ->setLength($row->pack_length)
                ->setWidth($row->pack_width);

        $this->_em->persist($item);
    }

    private function _generateSalesOrders() {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:SalesOrder');
        $itemRep = $this->_em->getRepository('AppBundle:SalesOrderItem');

        $this->_output->writeln("Updating sales orders");

        $firstOpenSalesOrder = $rep->findOneBy(array('open' => true), array('orderNumber' => 'ASC'));

        if ($firstOpenSalesOrder === null) {

            $erpOrders = $erpRep->findBy(array('recordType' => 'O'));
        } else {

            $erpOrders = $erpRep->createQueryBuilder('e')
                    ->where("e.recordType = 'O'")
                    ->andWhere('e.orderNumber > :orderNumber')
                    ->setParameter('orderNumber', $firstOpenSalesOrder->getOrderNumber())
                    ->getQuery()
                    ->getResult();
        }

        $count = 0;
        $blockSize = 1000;

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

            $this->_generateInvoices($so);
            $this->_generateShipments($so);
            $this->_generateCredits($so);
            $this->_generatePackages($so);

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'O'));

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
            }

            $this->_em->persist($so);

            $count++;

            if (($count % $blockSize) == 0) {
                $this->_output->writeln("{$count} Sales Orders Updated");
                $this->_em->flush();
                $this->_em->clear();
            }
        }

        $this->_em->flush();
        $this->_em->clear();

        $this->_output->writeln("\nDone updating sales orders");
    }

    private function _generateInvoices(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'I', 'orderNumber' => $salesOrder->getOrderNumber()));

        foreach ($erpOrders as $t) {

            $invoice = $rep->findOneBy(array('orderNumber' => $t->getOrderNumber(), 'recordSequence' => $t->getRecordSequence()));

            if ($invoice === null) {
                $invoice = new Invoice();
            } elseif (!$invoice->getOpen()) {
                continue;
            }

            $invoice->setOrderNumber($t->getOrderNumber())
                    ->setRecordSequence($t->getRecordSequence())
                    ->setOpen($t->getOpen())
                    ->setStatus($t->getStatus())
                    ->setSalesOrder($salesOrder);

            $erpItems = $erpItemRep->findBy(array(
                'orderNumber' => $t->getOrderNumber(),
                'recordSequence' => $t->getRecordSequence(),
                'recordType' => 'I'));

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
            }

            $this->_em->persist($invoice);
        }
    }

    private function _generateShipments(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Shipment');
        $itemRep = $this->_em->getRepository('AppBundle:ShipmentItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'S', 'orderNumber' => $salesOrder->getOrderNumber()));

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
            }

            $this->_em->persist($shipment);
        }
    }

    private function _generateCredits(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpOrder');
        $erpItemRep = $this->_em->getRepository('AppBundle:ErpItem');
        $rep = $this->_em->getRepository('AppBundle:Credit');
        $itemRep = $this->_em->getRepository('AppBundle:CreditItem');

        $erpOrders = $erpRep->findBy(array('recordType' => 'C', 'orderNumber' => $salesOrder->getOrderNumber()));

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
            }

            $this->_em->persist($credit);
        }
    }

    private function _generatePackages(SalesOrder $salesOrder) {

        $erpRep = $this->_em->getRepository('AppBundle:ErpPackage');
        $rep = $this->_em->getRepository('AppBundle:Package');

        $erpOrders = $erpRep->findBy(array('orderNumber' => $salesOrder->getOrderNumber()));

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
            }
        }
    }

}
