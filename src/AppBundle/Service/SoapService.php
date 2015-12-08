<?php

namespace AppBundle\Service;

use AppBundle\Entity\Credit;
use AppBundle\Entity\CreditItem;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceItem;
use AppBundle\Entity\Manufacturer;
use AppBundle\Entity\Package;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\SalesOrderItem;
use AppBundle\Entity\Shipment;
use AppBundle\Entity\ShipmentItem;
use DateTime;
use Doctrine\ORM\EntityManager;

class SoapService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
    }

    /**
     * @WebMethod
     * 
     * @param wrapper[] $products @className=\AppBundle\Soap\SoapProduct
     * 
     * @return int $count
     */
    public function updateProducts($products) {

        $count = 0;

        foreach ($products as $p) {

            if (is_array($p)) {
                return $this->updateProducts($p);
            }

            $dbProduct = $this->_em->getRepository('AppBundle:Product')->findOneBySku($p->sku);

            if ($dbProduct === null) {
                $dbProduct = new Product();
                $dbProduct->setSku($p->sku);
            }

            $dbProduct->setName($p->name);
            $dbProduct->setPrice($p->price);
            $dbProduct->setStockQuantity($p->stockQuantity);
            
            $releaseDate = new DateTime($p->releaseDate);
            
            if ($releaseDate != $dbProduct->getReleaseDate()) {
                $dbProduct->setReleaseDate($releaseDate);
            }

            $manufacturer = $this->_em->getRepository('AppBundle:Manufacturer')->findOneByCode($p->manufacturerCode);

            if ($manufacturer === null) {
                $manufacturer = new Manufacturer();
                $manufacturer->setCode($p->manufacturerCode);
                $manufacturer->setName($p->manufacturerCode);
                $this->_em->persist($manufacturer);
                $this->_em->flush($manufacturer);
            }

            $dbProduct->setManufacturer($manufacturer);

            $productType = $this->_em->getRepository('AppBundle:ProductType')->findOneByCode($p->productTypeCode);

            if ($productType === null) {
                $productType = new ProductType();
                $productType->setCode($p->productTypeCode);
                $productType->setName($p->productTypeCode);
                $this->_em->persist($productType);
                $this->_em->flush($productType);
            }

            $dbProduct->setProductType($productType);

            $this->_em->persist($dbProduct);

            $count++;
        }

        $this->_em->flush();

        return $count;
    }

    /**
     * @WebMethod
     * 
     * @param wrapper[] $salesOrders @className=\AppBundle\Soap\SoapSalesOrder
     * 
     * @return int $count
     */
    public function updateSalesOrders($salesOrders) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        foreach ($salesOrders as $so) {

            if (is_array($so)) {
                return $this->updateSalesOrders($so);
            }

            if (!isset($so->orderNumber)) {
                continue;
            }

            $salesOrder = $rep->findOneBy(array('orderNumber' => $so->orderNumber));

            if ($salesOrder === null) {
                $salesOrder = new SalesOrder();
                $salesOrder->setOrderNumber($so->orderNumber);
                $salesOrder->setRecordSequence($so->recordSequence);
            }

            $salesOrder->setCustomerNumber($so->customerNumber);
            $salesOrder->setCustomerPO($so->customerPO);
            $salesOrder->setExternalOrderNumber($so->externalOrderNumber);
            $salesOrder->setOpen($so->open);
            
            $orderDate = new DateTime($so->orderDate);
            
            if ($orderDate != $salesOrder->getOrderDate()) {
                $salesOrder->setOrderDate($orderDate);
            }
            
            $salesOrder->setOrderGrossAmount($so->orderGrossAmount);
            $salesOrder->setShipToAddress1($so->shipToAddress1);
            $salesOrder->setShipToAddress2($so->shipToAddress2);
            $salesOrder->setShipToAddress3($so->shipToAddress3);
            $salesOrder->setShipToCity($so->shipToCity);
            $salesOrder->setShipToCountryCode($so->shipToCountryCode);
            $salesOrder->setShipToName($so->shipToName);
            $salesOrder->setShipToPostalCode($so->shipToPostalCode);
            $salesOrder->setShipToState($so->shipToState);
            $salesOrder->setShipViaCode($so->shipViaCode);
            $salesOrder->setStatus($so->status);

            $this->_em->persist($salesOrder);

            // load items
            $itemRep = $this->_em->getRepository('AppBundle:SalesOrderItem');

            if (is_array($so->salesOrderItems->salesOrderItem)) {
                $salesOrderItems = $so->salesOrderItems->salesOrderItem;
            } else {
                $salesOrderItems = $so->salesOrderItems;
            }

            foreach ($salesOrderItems as $t) {
                $item = $itemRep->findOneBy(array('salesOrder' => $salesOrder, 'lineNumber' => $t->lineNumber));
                if ($item === null) {
                    $item = new SalesOrderItem();
                    $item->setSalesOrder($salesOrder);
                    $item->setLineNumber($t->lineNumber);
                }
                $item->setItemNumber($t->itemNumber);
                $item->setName($t->name);
                $item->setPrice($t->price);
                $item->setQuantityOrdered($t->quantityOrdered);
                $this->_em->persist($item);
            }

            if (isset($so->shipments->shipment)) {
                $this->_updateShipments($salesOrder, $so);
            }

            if (isset($so->invoices->invoice)) {
                $this->_updateInvoices($salesOrder, $so);
            }

            if (isset($so->credits->credit)) {
                $this->_updateCredits($salesOrder, $so);
            }

            if (isset($so->packages->package)) {
                $this->_updatePackages($salesOrder, $so);
            }

            $this->_em->persist($salesOrder);

            $this->_em->flush();
        }
    }

    /**
     * @WebMethod
     * 
     * @param wrapper[] $invoices @className=\AppBundle\Soap\SoapInvoice
     * 
     * @return int $count
     */
    public function updateConsolidatedInvoices($invoices) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        if (is_array($invoices->invoice)) {
            $invoices = $invoices->invoice;
        } else {
            $invoices = $so->invoices;
        }

        foreach ($invoices as $i) {
            $invoice = $rep->findOneBy(array('orderNumber' => $i->orderNumber, 'recordSequence' => $i->recordSequence));
            if ($invoice === null) {
                $invoice = new Invoice();
                $invoice->setOrderNumber($i->orderNumber);
                $invoice->setRecordSequence($i->recordSequence);
            }
            $invoice->setConsolidated($i->consolidated);
            $invoice->setCustomerNumber($i->customerNumber);
            $invoice->setFreightCharge($i->freightCharge);
            $invoice->setGrossAmount($i->grossAmount);
            
            $invoiceDate = new DateTime($i->invoiceDate);
            
            if ($invoiceDate != $invoice->getInvoiceDate()) {            
                $invoice->setInvoiceDate($invoiceDate);
            }
            
            $invoice->setInvoiceNumber($i->invoiceNumber);
            $invoice->setNetAmount($i->netAmount);
            $invoice->setOpen($i->open);
            $invoice->setShippingAndHandlingCharge($i->shippingAndHandlingCharge);
            $invoice->setStatus($i->status);
            
            $children = $rep->findBy(array('invoiceNumber' => $i->invoiceNumber));
                        
            foreach ($children as $child) {
                $invoice->getChildren()->add($child);
                $invoice->getConsolidatedSalesOrders()->add($child->getSalesOrder());
            }
            
            $this->_em->persist($invoice);

            if (is_array($i->invoiceItems->invoiceItem)) {
                $invoiceItems = $i->invoiceItems->invoiceItem;
            } else {
                $invoiceItems = $i->invoiceItems;
            }

            foreach ($invoiceItems as $t) {
                $item = $itemRep->findOneBy(array('invoice' => $invoice, 'lineNumber' => $t->lineNumber));
                if ($item === null) {
                    $item = new InvoiceItem();
                    $item->setInvoice($invoice);
                    $item->setLineNumber($t->lineNumber);
                }
                $item->setItemNumber($t->itemNumber);
                $item->setName($t->name);
                $item->setPrice($t->price);
                $item->setQuantityOrdered($t->quantityOrdered);
                $item->setQuantityBilled($t->quantityBilled);
                $item->setQuantityShipped($t->quantityShipped);
                $this->_em->persist($item);
            }
        }
    }

    private function _updatePackages(SalesOrder $salesOrder, $so) {

        $rep = $this->_em->getRepository('AppBundle:Package');

        if (is_array($so->packages->package)) {
            $packages = $so->packages->package;
        } else {
            $packages = $so->packages;
        }

        foreach ($packages as $i) {

            $package = $rep->findOneBy(array('salesOrder' => $salesOrder, 'trackingNumber' => $i->trackingNumber));
            if ($package === null) {
                $package = new Package();
                $package->setSalesOrder($salesOrder);
                $package->setOrderNumber($i->orderNumber);
                $package->setManifestId($i->manifestId);
                $package->setRecordSequence($i->recordSequence);
                $package->setTrackingNumber($i->trackingNumber);
            }
            $package->setPackageCharge($i->packageCharge);
            $package->setHeight($i->height);
            $package->setLength($i->length);
            $package->setShipViaCode($i->shipViaCode);
            $package->setWeight($i->weight);
            $package->setWidth($i->width);

            $this->_em->persist($package);
        }
    }

    private function _updateCredits(SalesOrder $salesOrder, $so) {

        $rep = $this->_em->getRepository('AppBundle:Credit');
        $itemRep = $this->_em->getRepository('AppBundle:CreditItem');

        if (is_array($so->credits->credit)) {
            $credits = $so->credits->credit;
        } else {
            $credits = $so->credits;
        }

        foreach ($credits as $i) {

            $credit = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->recordSequence));
            if ($credit === null) {
                $credit = new Credit();
                $credit->setSalesOrder($salesOrder);
                $credit->setOrderNumber($i->orderNumber);
                $credit->setRecordSequence($i->recordSequence);
            }
            $credit->setOpen($i->open);
            $credit->setStatus($i->status);
            $this->_em->persist($credit);

            if (is_array($i->creditItems->creditItem)) {
                $creditItems = $i->creditItems->creditItem;
            } else {
                $creditItems = $i->creditItems;
            }

            foreach ($creditItems as $t) {
                $item = $itemRep->findOneBy(array('credit' => $credit, 'lineNumber' => $t->lineNumber));
                if ($item === null) {
                    $item = new CreditItem();
                    $item->setCredit($credit);
                    $item->setLineNumber($t->lineNumber);
                }
                $item->setItemNumber($t->itemNumber);
                $item->setName($t->name);
                $item->setPrice($t->price);
                $item->setQuantityOrdered($t->quantityOrdered);
                $item->setQuantityCredited($t->quantityCredited);
                $this->_em->persist($item);
            }
        }
    }

    private function _updateShipments(SalesOrder $salesOrder, $so) {

        $rep = $this->_em->getRepository('AppBundle:Shipment');
        $itemRep = $this->_em->getRepository('AppBundle:ShipmentItem');

        if (is_array($so->shipments->shipment)) {
            $shipments = $so->shipments->shipment;
        } else {
            $shipments = $so->shipments;
        }

        foreach ($shipments as $i) {
            $shipment = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->recordSequence));
            if ($shipment === null) {
                $shipment = new Shipment();
                $shipment->setSalesOrder($salesOrder);
                $shipment->setOrderNumber($i->orderNumber);
                $shipment->setRecordSequence($i->recordSequence);
            }
            $shipment->setOpen($i->open);
            $shipment->setStatus($i->status);
            $this->_em->persist($shipment);

            if (is_array($i->shipmentItems->shipmentItem)) {
                $shipmentItems = $i->shipmentItems->shipmentItem;
            } else {
                $shipmentItems = $i->shipmentItems;
            }

            foreach ($shipmentItems as $t) {
                $item = $itemRep->findOneBy(array('shipment' => $shipment, 'lineNumber' => $t->lineNumber));
                if ($item === null) {
                    $item = new ShipmentItem();
                    $item->setShipment($shipment);
                    $item->setLineNumber($t->lineNumber);
                }
                $item->setItemNumber($t->itemNumber);
                $item->setName($t->name);
                $item->setPrice($t->price);
                $item->setQuantityOrdered($t->quantityOrdered);
                $item->setQuantityShipped($t->quantityShipped);
                $this->_em->persist($item);
            }
        }
    }

    private function _updateInvoices(SalesOrder $salesOrder, $so) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        if (is_array($so->invoices->invoice)) {
            $invoices = $so->invoices->invoice;
        } else {
            $invoices = $so->invoices;
        }

        foreach ($invoices as $i) {
            $invoice = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->recordSequence));
            if ($invoice === null) {
                $invoice = new Invoice();
                $invoice->setSalesOrder($salesOrder);
                $invoice->setOrderNumber($i->orderNumber);
                $invoice->setRecordSequence($i->recordSequence);
            }
            $invoice->setConsolidated($i->consolidated);
            $invoice->setCustomerNumber($i->customerNumber);
            $invoice->setFreightCharge($i->freightCharge);
            $invoice->setGrossAmount($i->grossAmount);
            
            $invoiceDate = new DateTime($i->invoiceDate);
            
            if ($invoiceDate != $invoice->getInvoiceDate()) {            
                $invoice->setInvoiceDate($invoiceDate);
            }
            
            $invoice->setInvoiceNumber($i->invoiceNumber);
            $invoice->setNetAmount($i->netAmount);
            $invoice->setOpen($i->open);
            $invoice->setShippingAndHandlingCharge($i->shippingAndHandlingCharge);
            $invoice->setStatus($i->status);
            $this->_em->persist($invoice);

            if (is_array($i->invoiceItems->invoiceItem)) {
                $invoiceItems = $i->invoiceItems->invoiceItem;
            } else {
                $invoiceItems = $i->invoiceItems;
            }

            foreach ($invoiceItems as $t) {
                $item = $itemRep->findOneBy(array('invoice' => $invoice, 'lineNumber' => $t->lineNumber));
                if ($item === null) {
                    $item = new InvoiceItem();
                    $item->setInvoice($invoice);
                    $item->setLineNumber($t->lineNumber);
                }
                $item->setItemNumber($t->itemNumber);
                $item->setName($t->name);
                $item->setPrice($t->price);
                $item->setQuantityOrdered($t->quantityOrdered);
                $item->setQuantityBilled($t->quantityBilled);
                $item->setQuantityShipped($t->quantityShipped);
                $this->_em->persist($item);
            }
            
        }
    }

}
