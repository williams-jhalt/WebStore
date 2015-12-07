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
            $dbProduct->setReleaseDate(new DateTime($p->releaseDate));

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
     * @param string $orderNumber
     */
    public function updateSalesOrder($orderNumber) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

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

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {

            $salesOrder = $rep->findOneBy(array('orderNumber' => $i->oe_head_order));

            if ($salesOrder === null) {
                $salesOrder = new SalesOrder();
                $salesOrder->setOrderNumber($i->oe_head_order);
                $salesOrder->setRecordSequence($i->oe_head_rec_seq);
            }

            $salesOrder->setCustomerNumber($i->oe_head_customer);
            $salesOrder->setCustomerPO($i->oe_head_cu_po);
            $salesOrder->setExternalOrderNumber($i->oe_head_ord_ext);
            $salesOrder->setOpen($i->oe_head_opn);
            $salesOrder->setOrderDate(new DateTime($i->oe_head_ord_date));
            $salesOrder->setOrderGrossAmount($i->oe_head_o_tot_gross);
            $salesOrder->setShipToAddress1($i->oe_head_adr[0]);
            $salesOrder->setShipToAddress2($i->oe_head_adr[1]);
            $salesOrder->setShipToAddress3($i->oe_head_adr[2]);
            $salesOrder->setShipToCity($i->oe_head_adr[3]);
            $salesOrder->setShipToCountryCode($i->oe_head_country_code);
            $salesOrder->setShipToName($i->oe_head_name);
            $salesOrder->setShipToPostalCode($i->oe_head_postal_code);
            $salesOrder->setShipToState($i->oe_head_state);
            $salesOrder->setShipViaCode($i->oe_head_ship_via_code);
            $salesOrder->setStatus($i->oe_head_stat);

            $this->_em->persist($salesOrder);

            // load items
            $itemRep = $this->_em->getRepository('AppBundle:SalesOrderItem');

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$salesOrder->getOrderNumber()}' "
                    . "AND oe_line.rec_type = 'O'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields);

            foreach ($itemResponse as $t) {
                $item = $itemRep->findOneBy(array('salesOrder' => $salesOrder, 'lineNumber' => $t->oe_line_line));
                if ($item === null) {
                    $item = new SalesOrderItem();
                    $item->setSalesOrder($salesOrder);
                    $item->setLineNumber($t->oe_line_line);
                }
                $item->setItemNumber($t->oe_line_item);
                $item->setName(implode($t->oe_line_descr));
                $item->setPrice($t->oe_line_price);
                $item->setQuantityOrdered($t->oe_line_q_ord);
                $this->_em->persist($item);
            }

            $this->_updateShipments($salesOrder);
            $this->_updateInvoices($salesOrder);
            $this->_updateCredits($salesOrder);
            $this->_updatePackages($salesOrder);

            $this->_em->persist($salesOrder);
        }

        $this->_em->flush();
    }

    private function _updatePackages(SalesOrder $salesOrder) {

        $rep = $this->_em->getRepository('AppBundle:Package');

        $query = "FOR EACH oe_ship_pack "
                . "WHERE oe_ship_pack.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_ship_pack.order = '{$salesOrder->getOrderNumber()}' ";

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

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {

            $package = $rep->findOneBy(array('salesOrder' => $salesOrder, 'trackingNumber' => $i->oe_ship_pack_tracking_no));
            if ($package === null) {
                $package = new Package();
                $package->setSalesOrder($salesOrder);
                $package->setOrderNumber($i->oe_ship_pack_order);
                $package->setManifestId($i->oe_ship_pack_Manifest_id);
                $package->setRecordSequence($i->oe_ship_pack_rec_seq);
                $package->setTrackingNumber($i->oe_ship_pack_tracking_no);
            }
            $package->setPackageCharge($i->oe_ship_pack_pkg_chg);
            $package->setHeight($i->oe_ship_pack_pack_height);
            $package->setLength($i->oe_ship_pack_pack_length);
            $package->setShipViaCode($i->oe_ship_pack_ship_via_code);
            $package->setWeight($i->oe_ship_pack_pack_weight);
            $package->setWidth($i->oe_ship_pack_pack_width);

            $this->_em->persist($package);
        }
    }

    private function _updateCredits(SalesOrder $salesOrder) {

        $rep = $this->_em->getRepository('AppBundle:Credit');
        $itemRep = $this->_em->getRepository('AppBundle:CreditItem');

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$salesOrder->getOrderNumber()}' "
                . "AND oe_head.rec_type = 'C'";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.rec_seq,"
                . "oe_head.stat";

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {
            $credit = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->oe_head_rec_seq));
            if ($credit === null) {
                $credit = new Credit();
                $credit->setSalesOrder($salesOrder);
                $credit->setOrderNumber($i->oe_head_order);
                $credit->setRecordSequence($i->oe_head_rec_seq);
            }
            $credit->setOpen($i->oe_head_opn);
            $credit->setStatus($i->oe_head_stat);
            $this->_em->persist($credit);

            // load invoice items

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$salesOrder->getOrderNumber()}' "
                    . "AND oe_line.rec_seq = '{$credit->getRecordSequence()}' "
                    . "AND oe_line.rec_type = 'C'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields);

            foreach ($itemResponse as $t) {
                $item = $itemRep->findOneBy(array('credit' => $credit, 'lineNumber' => $t->oe_line_line));
                if ($item === null) {
                    $item = new CreditItem();
                    $item->setCredit($credit);
                    $item->setLineNumber($t->oe_line_line);
                }
                $item->setItemNumber($t->oe_line_item);
                $item->setName(implode($t->oe_line_descr));
                $item->setPrice($t->oe_line_price);
                $item->setQuantityOrdered($t->oe_line_q_ord);
                $item->setQuantityCredited($t->oe_line_q_comm);
                $this->_em->persist($item);
            }
        }
    }

    private function _updateShipments(SalesOrder $salesOrder) {

        $rep = $this->_em->getRepository('AppBundle:Shipment');
        $itemRep = $this->_em->getRepository('AppBundle:ShipmentItem');

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$salesOrder->getOrderNumber()}' "
                . "AND oe_head.rec_type = 'S'";

        $fields = "oe_head.order,"
                . "oe_head.opn,"
                . "oe_head.rec_seq,"
                . "oe_head.stat";

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {
            $shipment = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->oe_head_rec_seq));
            if ($shipment === null) {
                $shipment = new Shipment();
                $shipment->setSalesOrder($salesOrder);
                $shipment->setOrderNumber($i->oe_head_order);
                $shipment->setRecordSequence($i->oe_head_rec_seq);
            }
            $shipment->setOpen($i->oe_head_opn);
            $shipment->setStatus($i->oe_head_stat);
            $this->_em->persist($shipment);

            // load invoice items

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$salesOrder->getOrderNumber()}' "
                    . "AND oe_line.rec_seq = '{$shipment->getRecordSequence()}' "
                    . "AND oe_line.rec_type = 'S'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields);

            foreach ($itemResponse as $t) {
                $item = $itemRep->findOneBy(array('shipment' => $shipment, 'lineNumber' => $t->oe_line_line));
                if ($item === null) {
                    $item = new ShipmentItem();
                    $item->setShipment($shipment);
                    $item->setLineNumber($t->oe_line_line);
                }
                $item->setItemNumber($t->oe_line_item);
                $item->setName(implode($t->oe_line_descr));
                $item->setPrice($t->oe_line_price);
                $item->setQuantityOrdered($t->oe_line_q_ord);
                $item->setQuantityShipped($t->oe_line_q_comm);
                $this->_em->persist($item);
            }
        }
    }

    private function _updateConsolidatedInvoice(Invoice $invoice) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.invoice = '{$invoice->getInvoiceNumber()}' "
                . "AND oe_head.consolidated_order = yes ";

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

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {
            $parent = $rep->findOneBy(array('invoiceNumber' => $invoice->getInvoiceNumber(), 'consolidated' => true));
            if ($parent === null) {
                $parent = new Invoice();
                $parent->setOrderNumber($i->oe_head_order);
                $parent->setRecordSequence($i->oe_head_rec_seq);
            }
            $parent->setConsolidated($i->oe_head_consolidated_order);
            $parent->setCustomerNumber($i->oe_head_customer);
            $parent->setFreightCharge($i->oe_head_c_tot_code_amt[0]);
            $parent->setGrossAmount($i->oe_head_c_tot_gross);
            $parent->setInvoiceDate(new DateTime($i->oe_head_invc_date));
            $parent->setInvoiceNumber($i->oe_head_invoice);
            $parent->setNetAmount($i->oe_head_c_tot_net_ar);
            $parent->setOpen($i->oe_head_opn);
            $parent->setShippingAndHandlingCharge($i->oe_head_c_tot_code_amt[1]);
            $parent->setStatus($i->oe_head_stat);
            $parent->getChildren()->add($invoice);
            $parent->getConsolidatedSalesOrders()->add($invoice->getSalesOrder());
            $this->_em->persist($parent);

            // load invoice items

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$parent->getOrderNumber()}' "
                    . "AND oe_line.rec_seq = '{$parent->getRecordSequence()}' "
                    . "AND oe_line.rec_type = 'I'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_itd,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields);

            foreach ($itemResponse as $t) {
                $item = $itemRep->findOneBy(array('invoice' => $parent, 'lineNumber' => $t->oe_line_line));
                if ($item === null) {
                    $item = new InvoiceItem();
                    $item->setInvoice($parent);
                    $item->setLineNumber($t->oe_line_line);
                }
                $item->setItemNumber($t->oe_line_item);
                $item->setName(implode($t->oe_line_descr));
                $item->setPrice($t->oe_line_price);
                $item->setQuantityOrdered($t->oe_line_q_ord);
                $item->setQuantityBilled($t->oe_line_q_itd);
                $item->setQuantityShipped($t->oe_line_q_comm);
                $this->_em->persist($item);
            }
        }
    }

    private function _updateInvoices(SalesOrder $salesOrder) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');
        $itemRep = $this->_em->getRepository('AppBundle:InvoiceItem');

        $query = "FOR EACH oe_head "
                . "WHERE oe_head.company_oe = '{$this->_erp->getCompany()}' "
                . "AND oe_head.order = '{$salesOrder->getOrderNumber()}' "
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

        $response = $this->_erp->read($query, $fields);

        foreach ($response as $i) {
            $invoice = $rep->findOneBy(array('salesOrder' => $salesOrder, 'recordSequence' => $i->oe_head_rec_seq));
            if ($invoice === null) {
                $invoice = new Invoice();
                $invoice->setSalesOrder($salesOrder);
                $invoice->setOrderNumber($i->oe_head_order);
                $invoice->setRecordSequence($i->oe_head_rec_seq);
            }
            $invoice->setConsolidated($i->oe_head_consolidated_order);
            $invoice->setCustomerNumber($i->oe_head_customer);
            $invoice->setFreightCharge($i->oe_head_c_tot_code_amt[0]);
            $invoice->setGrossAmount($i->oe_head_c_tot_gross);
            $invoice->setInvoiceDate(new DateTime($i->oe_head_invc_date));
            $invoice->setInvoiceNumber($i->oe_head_invoice);
            $invoice->setNetAmount($i->oe_head_c_tot_net_ar);
            $invoice->setOpen($i->oe_head_opn);
            $invoice->setShippingAndHandlingCharge($i->oe_head_c_tot_code_amt[1]);
            $invoice->setStatus($i->oe_head_stat);
            $this->_em->persist($invoice);

            // load invoice items

            $itemQuery = "FOR EACH oe_line "
                    . "WHERE oe_line.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_line.order = '{$salesOrder->getOrderNumber()}' "
                    . "AND oe_line.rec_seq = '{$invoice->getRecordSequence()}' "
                    . "AND oe_line.rec_type = 'I'";

            $itemFields = "oe_line.line,"
                    . "oe_line.item,"
                    . "oe_line.descr,"
                    . "oe_line.price,"
                    . "oe_line.q_ord,"
                    . "oe_line.q_itd,"
                    . "oe_line.q_comm";

            $itemResponse = $this->_erp->read($itemQuery, $itemFields);

            foreach ($itemResponse as $t) {
                $item = $itemRep->findOneBy(array('invoice' => $invoice, 'lineNumber' => $t->oe_line_line));
                if ($item === null) {
                    $item = new InvoiceItem();
                    $item->setInvoice($invoice);
                    $item->setLineNumber($t->oe_line_line);
                }
                $item->setItemNumber($t->oe_line_item);
                $item->setName(implode($t->oe_line_descr));
                $item->setPrice($t->oe_line_price);
                $item->setQuantityOrdered($t->oe_line_q_ord);
                $item->setQuantityBilled($t->oe_line_q_itd);
                $item->setQuantityShipped($t->oe_line_q_comm);
                $this->_em->persist($item);
            }

            $this->_updateConsolidatedInvoice($invoice);
        }
    }

}
