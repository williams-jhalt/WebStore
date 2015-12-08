<?php

namespace AppBundle\Service;

use AppBundle\Entity\Invoice;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class InvoiceService {

    private $_em;

    public function __construct(EntityManager $em) {
        $this->_em = $em;
    }

    public function findBySearchOptions($params, $offset = 0, $limit = 10) {

        $repository = $this->_em->getRepository("AppBundle:Invoice");

        $qb = $repository->createQueryBuilder('p');

        if (isset($params['search_terms']) && !empty($params['search_terms'])) {
            $qb->join('p.salesOrder', 's');
            $qb->andWhere('p.orderNumber LIKE :searchTerms OR s.externalOrderNumber LIKE :searchTerms OR s.customerPO LIKE :searchTerms')->setParameter('searchTerms', $params['search_terms'] . '%');
        }        

        if (isset($params['customer_numbers']) && !empty($params['customer_numbers'])) {
            $qb->andWhere('p.customerNumber IN (:customerNumbers)')->setParameter('customerNumbers', $params['customer_numbers']);
        }

        if (isset($params['open'])) {
            $qb->andWhere('p.open = :open')->setParameter('open', $params['open']);
        }
        
        if (isset($params['consolidated'])) {
            $qb->andWhere('p.consolidated = :consolidated')->setParameter('consolidated', $params['consolidated']);
        }        

        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->orderBy('p.orderNumber', 'DESC');

        $query = $qb->getQuery();

        $orders = $query->getResult();

        return $orders;
    }

    public function findAll($offset = 0, $limit = 10) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');

        $orders = $rep->findBy(array(), array('orderNumber' => 'DESC'), $limit, $offset);

        return $orders;
    }

    public function find($id) {

        $rep = $this->_em->getRepository('AppBundle:Invoice');

        $order = $rep->find($id);

        return $order;
    }

}
