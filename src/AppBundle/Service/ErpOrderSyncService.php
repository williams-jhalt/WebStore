<?php

namespace AppBundle\Service;

use AppBundle\Soap\SoapCredit;
use AppBundle\Soap\SoapCreditItem;
use AppBundle\Soap\SoapInvoice;
use AppBundle\Soap\SoapInvoiceItem;
use AppBundle\Soap\SoapPackage;
use AppBundle\Soap\SoapSalesOrder;
use AppBundle\Soap\SoapSalesOrderItem;
use AppBundle\Soap\SoapShipment;
use AppBundle\Soap\SoapShipmentItem;
use Doctrine\ORM\EntityManager;
use SoapClient;
use SoapFault;
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
    private $_wsdlLocation;
    private $_soapClient;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $wsdlLocation, $soapUser, $soapPass) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_wsdlLocation = $wsdlLocation;

        $this->_soapClient = new SoapClient($this->_wsdlLocation, array(
            'login' => $soapUser,
            'password' => $soapPass,
            'keep_alive' => true,
            'trace' => 1));
    }

    public function updateOpenOrders(OutputInterface $output) {

        $output->writeln("Beginning update of current open orders");

        $orderNumbers = $this->_em->createQuery('SELECT o.orderNumber FROM AppBundle:SalesOrder o WHERE o.open = 1')->getResult();

        $batch = 0;
        $batchSize = 25;

        $ch = curl_init();

        while ($batch < sizeof($orderNumbers)) {

            $salesOrders = array();

            $batchOrderNumbers = array_slice($orderNumbers, $batch, $batchSize);

            foreach ($batchOrderNumbers as $orderNumber) {
                $salesOrders[] = $this->_generateSoapSalesOrder($orderNumber['orderNumber'], $ch);
            }

            try {
                $this->_soapClient->updateSalesOrders(array('salesOrders' => $salesOrders));
            } catch (SoapFault $fault) {
                $output->writeln("REQUEST:\n" . $this->_soapClient->__getLastRequest());
                $output->writeln("Couldn't submit webservice call " . $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        };

        curl_close($ch);
    }

    public function loadNewOrders(OutputInterface $output) {

        $output->writeln("Begin import of new orders");

        $knownOrderNumbers = $this->_em->createQuery("SELECT o.orderNumber FROM AppBundle:SalesOrder o WHERE DATE_DIFF(CURRENT_DATE(), o.orderDate) <= 1")->getResult();

        $query = "FOR EACH oe_head NO-LOCK WHERE "
                . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.rec_type = 'O' "
                . "AND INTERVAL(NOW, oe_head.ord_date, 'days') <= 1 ";

        $fields = "oe_head.order";

        $batch = 0;
        $batchSize = 25;

        $ch = curl_init();

        do {

            $result = $this->_erp->read($query, $fields, $batch, $batchSize, $ch);

            $salesOrders = array();

            foreach ($result as $item) {
                if (in_array(array('orderNumber' => $item->oe_head_order), $knownOrderNumbers)) {
                    continue;
                }
                $salesOrders[] = $this->_generateSoapSalesOrder($item->oe_head_order, $ch);
            }

            try {
                $this->_soapClient->updateSalesOrders(array('salesOrders' => $salesOrders));
            } catch (SoapFault $fault) {
                $output->writeln("REQUEST:\n" . $this->_soapClient->__getLastRequest());
                $output->writeln("Couldn't submit webservice call " . $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        } while (!empty($result));

        curl_close($ch);
    }

    public function loadConsolidatedInvoices(OutputInterface $output) {

        $output->writeln("Begin import of consolidated invoices");

        $knownOrderNumbers = $this->_em->createQuery("SELECT o.orderNumber, o.recordSequence FROM AppBundle:Invoice o WHERE o.consolidated = 1 AND DATE_DIFF(CURRENT_DATE(), o.invoiceDate) <= 1")->getResult();

        $query = "FOR EACH oe_head NO-LOCK WHERE "
                . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.rec_type = 'I' "
                . "AND oe_head.consolidated_order = yes "
                . "AND INTERVAL(NOW, oe_head.invc_date, 'days') <= 1 ";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.consolidated_order,"
                . "oe_head.invc_date,"
                . "oe_head.invoice,"
                . "oe_head.c_tot_code_amt,"
                . "oe_head.c_tot_gross,"
                . "oe_head.c_tot_net_ar,"
                . "oe_head.rec_seq,"
                . "oe_head.stat,"
                . "oe_head.customer";

        $batch = 0;
        $batchSize = 25;

        $ch = curl_init();

        do {

            $response = $this->_erp->read($query, $fields, $batch, $batchSize, $ch);

            $invoices = array();

            foreach ($response as $i) {

                if (in_array(array(
                            'orderNumber' => $i->oe_head_order,
                            'recordSequence' => $i->oe_head_rec_seq), $knownOrderNumbers)) {
                    continue;
                }

                $invoice = new SoapInvoice();
                $invoice->orderNumber = $i->oe_head_order;
                $invoice->recordSequence = $i->oe_head_rec_seq;
                $invoice->consolidated = $i->oe_head_consolidated_order;
                $invoice->customerNumber = $i->oe_head_customer;
                $invoice->freightCharge = $i->oe_head_c_tot_code_amt[0];
                $invoice->grossAmount = $i->oe_head_c_tot_gross;
                $invoice->invoiceDate = $i->oe_head_invc_date;
                $invoice->invoiceNumber = $i->oe_head_invoice;
                $invoice->netAmount = $i->oe_head_c_tot_net_ar;
                $invoice->open = $i->oe_head_opn;
                $invoice->shippingAndHandlingCharge = $i->oe_head_c_tot_code_amt[1];
                $invoice->status = $i->oe_head_stat;

                $itemQuery = "FOR EACH oe_line "
                        . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                        . "AND oe_line.order = '{$invoice->orderNumber}' "
                        . "AND oe_line.rec_seq = '{$invoice->recordSequence}' "
                        . "AND oe_line.rec_type = 'I'";

                $itemFields = "oe_line.line,"
                        . "oe_line.item,"
                        . "oe_line.descr,"
                        . "oe_line.price,"
                        . "oe_line.q_ord,"
                        . "oe_line.q_itd,"
                        . "oe_line.q_comm";

                $itemResponse = $this->_erp->read($itemQuery, $itemFields, 0, 5000, $ch);

                $invoice->invoiceItems = array();

                foreach ($itemResponse as $t) {
                    $item = new SoapInvoiceItem();
                    $item->lineNumber = $t->oe_line_line;
                    $item->itemNumber = $t->oe_line_item;
                    $item->name = implode($t->oe_line_descr);
                    $item->price = $t->oe_line_price;
                    $item->quantityOrdered = $t->oe_line_q_ord;
                    $item->quantityBilled = $t->oe_line_q_itd;
                    $item->quantityShipped = $t->oe_line_q_comm;
                    $invoice->invoiceItems[] = $item;
                }

                $invoices[] = $invoice;
            }

            if (sizeof($invoices) > 0) {
                try {
                    $this->_soapClient->updateConsolidatedInvoices(array('invoices' => $invoices));
                } catch (SoapFault $fault) {
                    $output->writeln("REQUEST:\n" . $this->_soapClient->__getLastRequest());
                    $output->writeln("Couldn't submit webservice call " . $fault->getMessage());
                }
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        } while (!empty($response));

        curl_close($ch);
    }

    private function _generateSoapSalesOrder($orderNumber, $ch) {

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$orderNumber}' "
                . "AND oe_head.rec_type = 'O'";

        $fields = "oe_head.order,"
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
                . "oe_head.rec_seq";

        $response = $this->_erp->read($query, $fields, 0, 100, $ch);

        foreach ($response as $i) {

            $salesOrder = new SoapSalesOrder();
            $salesOrder->orderNumber = $i->oe_head_order;
            $salesOrder->recordSequence = $i->oe_head_rec_seq;
            $salesOrder->customerNumber = $i->oe_head_customer;
            $salesOrder->customerPO = $i->oe_head_cu_po;
            $salesOrder->externalOrderNumber = $i->oe_head_ord_ext;
            $salesOrder->open = $i->oe_head_opn;
            $salesOrder->orderDate = $i->oe_head_ord_date;
            $salesOrder->orderGrossAmount = $i->oe_head_o_tot_gross;
            $salesOrder->shipToAddress1 = $i->oe_head_adr[0];
            $salesOrder->shipToAddress2 = $i->oe_head_adr[1];
            $salesOrder->shipToAddress3 = $i->oe_head_adr[2];
            $salesOrder->shipToCity = $i->oe_head_adr[3];
            $salesOrder->shipToCountryCode = $i->oe_head_country_code;
            $salesOrder->shipToName = $i->oe_head_name;
            $salesOrder->shipToPostalCode = $i->oe_head_postal_code;
            $salesOrder->shipToState = $i->oe_head_state;
            $salesOrder->shipViaCode = $i->oe_head_ship_via_code;
            $salesOrder->status = $i->oe_head_stat;

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$salesOrder->orderNumber}' "
                    . "AND oe_line.rec_seq = '{$salesOrder->recordSequence}' "
                    . "AND oe_line.rec_type = 'O'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields, 0, 1000, $ch);

            $salesOrder->salesOrderItems = array();

            foreach ($itemResponse as $t) {
                $item = new SoapSalesOrderItem();
                $item->lineNumber = $t->oe_line_line;
                $item->itemNumber = $t->oe_line_item;
                $item->name = implode($t->oe_line_descr);
                $item->price = $t->oe_line_price;
                $item->quantityOrdered = $t->oe_line_q_ord;
                $salesOrder->salesOrderItems[] = $item;
            }

            $salesOrder->shipments = $this->_generateSoapShipments($orderNumber, $ch);
            $salesOrder->invoices = $this->_generateSoapInvoices($orderNumber, $ch);
            $salesOrder->credits = $this->_generateSoapCredits($orderNumber, $ch);
            $salesOrder->packages = $this->_generateSoapPackages($orderNumber, $ch);

            return $salesOrder;
        }
    }

    private function _generateSoapShipments($orderNumber, $ch) {

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$orderNumber}' "
                . "AND oe_head.rec_type = 'S'";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.rec_seq,"
                . "oe_head.stat";

        $response = $this->_erp->read($query, $fields, 0, 100, $ch);

        $shipments = array();

        foreach ($response as $i) {

            $shipment = new SoapShipment();
            $shipment->orderNumber = $i->oe_head_order;
            $shipment->recordSequence = $i->oe_head_rec_seq;
            $shipment->open = $i->oe_head_opn;
            $shipment->status = $i->oe_head_stat;

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$shipment->orderNumber}' "
                    . "AND oe_line.rec_seq = '{$shipment->recordSequence}' "
                    . "AND oe_line.rec_type = 'S'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields, 0, 1000, $ch);

            $shipment->shipmentItems = array();

            foreach ($itemResponse as $t) {
                $item = new SoapShipmentItem();
                $item->lineNumber = $t->oe_line_line;
                $item->itemNumber = $t->oe_line_item;
                $item->name = implode($t->oe_line_descr);
                $item->price = $t->oe_line_price;
                $item->quantityOrdered = $t->oe_line_q_ord;
                $item->quantityShipped = $t->oe_line_q_comm;
                $shipment->shipmentItems[] = $item;
            }

            $shipments[] = $shipment;
        }

        return $shipments;
    }

    private function _generateSoapInvoices($orderNumber, $ch) {

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$orderNumber}' "
                . "AND oe_head.rec_type = 'I'";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.consolidated_order,"
                . "oe_head.invc_date,"
                . "oe_head.invoice,"
                . "oe_head.c_tot_code_amt,"
                . "oe_head.c_tot_gross,"
                . "oe_head.c_tot_net_ar,"
                . "oe_head.rec_seq,"
                . "oe_head.stat,"
                . "oe_head.customer";

        $response = $this->_erp->read($query, $fields, 0, 100, $ch);

        $invoices = array();

        foreach ($response as $i) {
            $invoice = new SoapInvoice();
            $invoice->orderNumber = $i->oe_head_order;
            $invoice->recordSequence = $i->oe_head_rec_seq;
            $invoice->consolidated = $i->oe_head_consolidated_order;
            $invoice->customerNumber = $i->oe_head_customer;
            $invoice->freightCharge = $i->oe_head_c_tot_code_amt[0];
            $invoice->grossAmount = $i->oe_head_c_tot_gross;
            $invoice->invoiceDate = $i->oe_head_invc_date;
            $invoice->invoiceNumber = $i->oe_head_invoice;
            $invoice->netAmount = $i->oe_head_c_tot_net_ar;
            $invoice->open = $i->oe_head_opn;
            $invoice->shippingAndHandlingCharge = $i->oe_head_c_tot_code_amt[1];
            $invoice->status = $i->oe_head_stat;

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$invoice->orderNumber}' "
                    . "AND oe_line.rec_seq = '{$invoice->recordSequence}' "
                    . "AND oe_line.rec_type = 'I'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_itd,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields, 0, 1000, $ch);

            $invoice->invoiceItems = array();

            foreach ($itemResponse as $t) {
                $item = new SoapInvoiceItem();
                $item->lineNumber = $t->oe_line_line;
                $item->itemNumber = $t->oe_line_item;
                $item->name = implode($t->oe_line_descr);
                $item->price = $t->oe_line_price;
                $item->quantityOrdered = $t->oe_line_q_ord;
                $item->quantityBilled = $t->oe_line_q_itd;
                $item->quantityShipped = $t->oe_line_q_comm;
                $invoice->invoiceItems[] = $item;
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    private function _generateSoapCredits($orderNumber, $ch) {

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$orderNumber}' "
                . "AND oe_head.rec_type = 'C'";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.rec_seq,"
                . "oe_head.stat";

        $response = $this->_erp->read($query, $fields, 0, 100, $ch);

        $credits = array();

        foreach ($response as $i) {

            $credit = new SoapCredit();
            $credit->orderNumber = $i->oe_head_order;
            $credit->recordSequence = $i->oe_head_rec_seq;
            $credit->open = $i->oe_head_opn;
            $credit->status = $i->oe_head_stat;

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$credit->orderNumber}' "
                    . "AND oe_line.rec_seq = '{$credit->recordSequence}' "
                    . "AND oe_line.rec_type = 'C'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields, 0, 1000, $ch);

            $credit->creditItems = array();

            foreach ($itemResponse as $t) {
                $item = new SoapCreditItem();
                $item->lineNumber = $t->oe_line_line;
                $item->itemNumber = $t->oe_line_item;
                $item->name = implode($t->oe_line_descr);
                $item->price = $t->oe_line_price;
                $item->quantityOrdered = $t->oe_line_q_ord;
                $item->quantityCredited = $t->oe_line_q_comm;
                $credit->creditItems[] = $item;
            }

            $credits[] = $credit;
        }

        return $credits;
    }

    private function _generateSoapPackages($orderNumber, $ch) {

        $query = "FOR EACH oe_ship_pack "
                . "WHERE oe_ship_pack.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_ship_pack.order = '{$orderNumber}' ";

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

        $response = $this->_erp->read($query, $fields, 0, 100, $ch);

        $packages = array();

        foreach ($response as $i) {

            $package = new SoapPackage();
            $package->orderNumber = $i->oe_ship_pack_order;
            $package->manifestId = $i->oe_ship_pack_Manifest_id;
            $package->recordSequence = $i->oe_ship_pack_rec_seq;
            $package->trackingNumber = $i->oe_ship_pack_tracking_no;
            $package->packageCharge = $i->oe_ship_pack_pkg_chg;
            $package->height = $i->oe_ship_pack_pack_height;
            $package->length = $i->oe_ship_pack_pack_length;
            $package->shipViaCode = $i->oe_ship_pack_ship_via_code;
            $package->weight = $i->oe_ship_pack_pack_weight;
            $package->width = $i->oe_ship_pack_pack_width;

            $packages[] = $package;
        }

        return $packages;
    }

}
