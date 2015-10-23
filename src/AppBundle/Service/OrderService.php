<?php

namespace AppBundle\Service;

use AppBundle\Entity\SalesOrder;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderService {

    private $_em;
    private $_erp;
    private $_company;

    public function __construct(EntityManager $em, ErpOrderSyncService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    public function findBySearchOptions(OrderSearchOptions $searchOptions, $offset = 0, $limit = 10) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        $params = array();

        if ($searchOptions->getOpen() !== null) {
            $params['open'] = $searchOptions->getOpen();
        }

        if ($searchOptions->getCustomerNumber() !== null) {
            $params['customerNumber'] = $searchOptions->getCustomerNumber();
        }

        $orders = $rep->findBy($params, array('orderNumber' => 'DESC'), $limit, $offset);

//        $timeAgo = new DateTime();
//        $timeAgo->sub(new DateInterval("PT15M"));
//
//        foreach ($orders as $order) {
//            if ($order->getOpen() && $order->getUpdatedOn() < $timeAgo) {
//                $this->_erp->updateOrder($order);
//            }
//        }

        return $orders;
    }

    public function findAll($offset = 0, $limit = 10) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        $orders = $rep->findBy(array(), array('orderNumber' => 'DESC'), $limit, $offset);

//        $timeAgo = new DateTime();
//        $timeAgo->sub(new DateInterval("PT15M"));
//
//        foreach ($orders as $order) {
//            if ($order->getOpen() && $order->getUpdatedOn() < $timeAgo) {
//                $this->_erp->updateOrder($order);
//            }
//        }

        return $orders;
    }
    
    public function getStatusCode(SalesOrder $so) {

        $timeAgo = new DateTime();
        $timeAgo->sub(new DateInterval("PT15M"));

        if ($so->getOpen() && $so->getUpdatedOn() < $timeAgo) {
            $this->_erp->updateOrder($so);
        }
        
        $status = "";
        
        if (sizeof($so->getShipments()) > 0) {
            $status .= "P ";
        }
        
        if (sizeof($so->getPackages()) > 0) {
            $status .= "S ";
        
        if (sizeof($so->getInvoices()) > 0) {
            $status .= "I";
        }
        }
        
        return $status;
        
    }

    public function find($orderNumber) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        $order = $rep->findOneBy(array('orderNumber' => $orderNumber));

        $timeAgo = new DateTime();
        $timeAgo->sub(new DateInterval("PT15M"));

        if ($order->getOpen() && $order->getUpdatedOn() < $timeAgo) {
            $this->_erp->updateOrder($order);
        }

        return $order;
    }

}
