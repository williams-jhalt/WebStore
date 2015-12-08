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

    public function __construct(EntityManager $em) {
        $this->_em = $em;
    }

    public function findBySearchOptions($params, $offset = 0, $limit = 10) {

        $repository = $this->_em->getRepository("AppBundle:SalesOrder");

        $qb = $repository->createQueryBuilder('p');

        if (isset($params['search_terms']) && !empty($params['search_terms'])) {
            $qb->andWhere('p.orderNumber LIKE :searchTerms OR p.externalOrderNumber LIKE :searchTerms OR p.customerPO LIKE :searchTerms')->setParameter('searchTerms', $params['search_terms'] . '%');
        }        

        if (isset($params['customer_numbers']) && !empty($params['customer_numbers'])) {
            $qb->andWhere('p.customerNumber IN (:customerNumbers)')->setParameter('customerNumbers', $params['customer_numbers']);
        }

        if (isset($params['open'])) {
            $qb->andWhere('p.open = :open')->setParameter('open', $params['open']);
        }

        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->orderBy('p.orderNumber', 'DESC');

        $query = $qb->getQuery();

        $orders = $query->getResult();

        return $orders;
    }

    public function findAll($offset = 0, $limit = 10) {

        $rep = $this->_em->getRepository('AppBundle:SalesOrder');

        $orders = $rep->findBy(array(), array('orderNumber' => 'DESC'), $limit, $offset);

        return $orders;
    }
    
    public function getStatusCode(SalesOrder $so) {
        
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

        return $order;
    }

}
