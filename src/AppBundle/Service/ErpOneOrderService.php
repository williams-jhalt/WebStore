<?php

namespace AppBundle\Service;

use AppBundle\Service\ErpOneConnectorService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErpOneOrderService
 *
 * @author johnh
 */
class ErpOneOrderService {

    private $_em;
    private $_erp;
    private $_fields = "Manifest_id,order,rec_type,rec_seq,customer,ord_date,cu_po,shipped,ship_date,invc_date,ship_atn,name,state,postal_code,country_code,adr,stat,ship_via_code,opn";

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {

        $this->_em = $em;
        $this->_erp = $erp;
    }

    private function _loadItemsFromErp($record) {

        $items = array();

        if (is_a($record, 'AppBundle\Entity\Order')) {

            $orderItemRep = $this->_em->getRepository('AppBundle:OrderItem');

            $query = "FOR EACH oe_line NO-LOCK WHERE order = '{$record->getOrderNumber()}' AND rec_type = 'O'";

            $itemResponse = $this->_erp->read($query);

            foreach ($itemResponse as $t) {
                $items[] = $orderItemRep->findOrCreate(array(
                    'sequenceNumber' => (string) $t->rec_seq,
                    'orderNumber' => (string) $t->order,
                    'lineNumber' => (string) $t->line,
                    'name' => implode(" ", $t->descr),
                    'itemNumber' => $t->item,
                    'price' => $t->price,
                    'quantityOrdered' => $t->q_ord,
                    'order' => $record
                ));
            }
        } elseif (is_a($record, 'AppBundle\Entity\Invoice')) {

            $invcItemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

            $query = "FOR EACH oe_line NO-LOCK WHERE order = '{$record->getOrderNumber()}' AND rec_type = 'I'";

            $itemResponse = $this->_erp->read($query);

            foreach ($itemResponse as $t) {
                $items[] = $invcItemRep->findOrCreate(array(
                    'sequenceNumber' => (string) $t->rec_seq,
                    'orderNumber' => (string) $t->order,
                    'lineNumber' => (string) $t->line,
                    'name' => implode(" ", $t->descr),
                    'itemNumber' => $t->item,
                    'quantityOrdered' => $t->q_ord,
                    'quantityBilled' => $t->q_itd,
                    'quantityShipped' => $t->q_comm,
                    'price' => $t->price,
                    'invoice' => $record
                ));
            }
        } elseif (is_a($record, 'AppBundle\Entity\Shipment')) {

            $shipItemRep = $this->_em->getRepository('AppBundle:ShipmentItem');

            $query = "FOR EACH oe_line NO-LOCK WHERE order = '{$record->getOrderNumber()}' AND rec_type = 'S'";

            $itemResponse = $this->_erp->read($query);

            foreach ($itemResponse as $t) {
                $items[] = $shipItemRep->findOrCreate(array(
                    'sequenceNumber' => (string) $t->rec_seq,
                    'orderNumber' => (string) $t->order,
                    'lineNumber' => (string) $t->line,
                    'name' => implode(" ", $t->descr),
                    'itemNumber' => $t->item,
                    'quantityOrdered' => $t->q_ord,
                    'quantityShipped' => $t->q_comm,
                    'price' => $t->price,
                    'shipment' => $record
                ));
            }
        }

        return $items;
    }

    private function _loadRecordFromErp($item) {

        $orderRep = $this->_em->getRepository('AppBundle:Order');

        $data = array(
            'orderNumber' => (string) $item->order,
            'customerNumber' => $item->customer,
            'orderDate' => new DateTime($item->ord_date),
            'reference1' => $item->cu_po,
            'shipToName' => $item->ship_atn,
            'shipToCompany' => $item->name,
            'shipToState' => $item->state,
            'shipToZip' => $item->postal_code,
            'shipToCountry' => $item->country_code,
            'shipToAddress1' => $item->adr[0],
            'shipToAddress2' => $item->adr[1],
            'shipToAddress3' => $item->adr[2],
            'shipToCity' => $item->adr[4],
            'status' => $item->stat,
            'shipViaCode' => $item->ship_via_code,
            'open' => $item->opn
        );

        $order = $orderRep->findOrCreate($data);

        if ($item->rec_type == 'O') {

            $order->loadFromArray($data);

            $this->_em->persist($order);
            $this->_em->flush($order);

            return $order;
        } elseif ($item->rec_type == 'S') {
            $shipRep = $this->_em->getRepository('AppBundle:Shipment');

            $data['manifestId'] = $item->Manifest_id;
            $data['sequenceNumber'] = $item->rec_seq;
            $data['shipped'] = $item->shipped;
            $data['order'] = $order;

            if (!empty($item->ship_date)) {
                $data['shipDate'] = new DateTime($item->ship_date);
            }

            return $shipRep->findOrUpdate($data);
        } elseif ($item->rec_type == 'I') {
            $invcRep = $this->_em->getRepository('AppBundle:Invoice');

            $data['manifestId'] = $item->Manifest_id;
            $data['sequenceNumber'] = $item->rec_seq;
            $data['order'] = $order;

            if (!empty($item->invc_date)) {
                $data['invoiceDate'] = new DateTime($item->invc_date);
            }

            return $invcRep->findOrUpdate($data);
        } else {

            return;
        }
    }

    /**
     * 
     * @param Weborder $weborder
     */
    public function submitOrder(Weborder $weborder) {

        // TODO: when distone is ready, this will submit an order, and return an order record from ERP
    }

    public function getPdf($manifestId, $recordType) {
        // TODO: when distone is ready, this will retrieve a PDF from the server
    }

    public function findAll($offset = 0, $limit = 100) {

        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC'", $this->_fields, $offset, $limit);

        $orders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $record = $this->_loadRecordFromErp($item);
            $record->setItems($this->_loadItemsFromErp($record));
            $orders[] = $record;
        }

        $this->_em->commit();

        return $orders;
    }

    public function findByOrderNumber($orderNumber) {

        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND order = '{$orderNumber}'", $this->_fields);

        $orders = array();

        $this->_em->beginTransaction();

        foreach ($response as $item) {
            $record = $this->_loadRecordFromErp($item);
            $record->setItems($this->_loadItemsFromErp($record));
            $orders[] = $record;
        }

        $this->_em->commit();

        return $orders;
    }

    public function getOrder($orderNumber) {

        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$orderNumber}'", $this->_fields);

        if (sizeof($response) == 0) {
            return null;
        }

        $order = $this->_loadRecordFromErp($response[0]);

        $order->setItems($this->_loadItemsFromErp($order));

        return $order;
    }

    public function findInvoices($orderNumber) {

        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND rec_type = 'I' AND order = '{$orderNumber}'", $this->_fields);

        $invoices = array();

        foreach ($response as $item) {

            $invoice = $this->_loadRecordFromErp($item);

            $invoice->setItems($this->_loadItemsFromErp($invoice));

            $invoices[] = $invoice;
        }

        return $invoices;
    }
    
    public function getInvoice($manifestId) {
        
        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND rec_type = 'I' AND Manifest_id = '{$manifestId}'", $this->_fields);
        
        if (sizeof($response) == 0) {
            return null;
        }
        
        $invoice = $this->_loadRecordFromErp($response[0]);
        $invoice->setItems($this->_loadItemsFromErp($invoice));
        
        return $invoice;
        
    }

    public function findShipments($orderNumber) {

        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND rec_type = 'S' AND order = '{$orderNumber}'", $this->_fields);

        $shipments = array();

        foreach ($response as $item) {

            $shipment = $this->_loadRecordFromErp($item);

            $shipment->setItems($this->_loadItemsFromErp($shipment));

            $shipments[] = $shipment;
        }

        return $shipments;
    }
    
    public function getShipment($manifestId) {
        
        $response = $this->_erp->read("FOR EACH oe_head WHERE company_oe = 'WTC' AND rec_type = 'S' AND Manifest_id = '{$manifestId}'", $this->_fields);
        
        if (sizeof($response) == 0) {
            return null;
        }
        
        $shipment = $this->_loadRecordFromErp($response[0]);
        $shipment->setItems($this->_loadItemsFromErp($shipment));
        
        return $shipment;
        
    }

    public function loadFromErpOne(DateTime $from, DateTime $to, OutputInterface $output) {

        $startDate = $from->format('m/d/Y');
        $endDate = $to->format('m/d/Y');

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND created_date >= '{$startDate}' AND created_date <= '{$endDate}'";

        $offset = 0;
        $limit = 1000;

        do {

            $end = ($limit + $offset);

            $output->writeln("\tProcessing... {$offset} to {$end}");

            $response = $this->_erp->read($query, $this->_fields, $offset, $limit);

            $i = 0;
            $blockSize = 250;

            $this->_em->beginTransaction();

            foreach ($response as $item) {
                $this->_loadRecordFromErp($item);
                if ($i % $blockSize) {
                    $this->_em->clear();
                }
                $i++;
            }

            $this->_em->commit();

            $offset = $offset + $limit;
        } while (!empty($response));
    }

}
