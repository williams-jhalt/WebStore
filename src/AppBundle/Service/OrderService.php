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
use Exception;
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

    public function findBySearchOptions(OrderSearchOptions $searchOptions, $offset, $limit) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        $params = array();

        if ($searchOptions->getOpen() !== null) {
            $params['open'] = $searchOptions->getOpen();
        }

        if ($searchOptions->getCustomerNumber() !== null) {
            $params['customerNumber'] = $searchOptions->getCustomerNumber();
        }

        $orders = $rep->findBy($params, array('orderNumber' => 'DESC'), $limit, $offset);

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

    public function find($orderNumber) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        return $rep->findOneBy(array('orderNumber' => $orderNumber));        
    }

    public function refreshOrders(OutputInterface $output) {

        $rep = $this->_em->getRepository('AppBundle:Order');

        $output->writeln("Refreshing current open order status and fetching new orders...");

        $oldestOpenOrder = $rep->findOneBy(array('open' => true), array('orderNumber' => 'ASC'));

        $timeCheck = new DateTime();
        $timeCheck->sub(new DateInterval('PT15M'));

        $query = "FOR EACH oe_head NO-LOCK WHERE company_oe = '{$this->_company}' AND rec_type = 'O' AND order > '{$oldestOpenOrder->getOrderNumber()}'";

        $response = $this->_erp->read($query, "cu_po,opn,ord_date,o_tot_gross,order,rec_seq,adr,country_code,postal_code,name,customer,stat,ord_ext");

        $count = sizeof($response);

        $output->writeln("There are {$count} orders to be imported...");

        foreach ($response as $item) {
            $output->write(".");
            $this->_loadFromErp($item);
        }

        $output->writeln("Finished refreshing orders");
    }

}
