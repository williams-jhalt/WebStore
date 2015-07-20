<?php

namespace AppBundle\Service;

use AppBundle\Entity\Invoice;
use DateTime;
use Doctrine\ORM\EntityManager;

class InvoiceService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    public function findAll($offset = 0, $limit = 100) {
        
        
        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' BY oe_head.order DESCENDING", "*", $offset, $limit
        );
                
        $repository = $this->em->getRepository('AppBundle:Invoice');
                
        $weborders = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $item) {
            $weborder = $repository->findOrUpdate(array(
                'orderNumber' => $item->order,
                'customerNumber' => $item->customer,
                'status' => $item->stat,
                'invoiceDate' => new DateTime($item->invc_date)
            ));
            $weborders[] = $weborder;
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function findByCustomer($customerNumber, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' AND customer = '{$customerNumber}' BY oe_head.order DESCENDING", "*", $offset, $limit
        );
                
        $repository = $this->em->getRepository('AppBundle:Invoice');
                
        $weborders = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $item) {
            $weborder = $repository->findOrUpdate(array(
                'orderNumber' => $item->order,
                'customerNumber' => $item->customer,
                'status' => $item->stat,
                'invoiceDate' => new DateTime($item->invc_date)
            ));
            $weborders[] = $weborder;
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function findByCustomerNumbers(array $customerNumbers, $offset = 0, $limit = 100) {
        
        $customerSelect = "";
        for ($i = 0; $i < count($customerNumbers); $i++) {
            $customerSelect .= " customer = '{$customerNumbers[$i]}' ";
            if ($i < count($customerNumbers) - 1) {
                $customerSelect .= " OR ";
            }
        }

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'I' "
                . "AND ({$customerSelect}) "
                . "BY oe_head.order DESCENDING", "*", $offset, $limit
        );
                
        $repository = $this->em->getRepository('AppBundle:Invoice');
                
        $weborders = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $item) {
            $weborder = $repository->findOrUpdate(array(
                'orderNumber' => $item->order,
                'customerNumber' => $item->customer,
                'status' => $item->stat,
                'invoiceDate' => new DateTime($item->invc_date)
            ));
            $weborders[] = $weborder;
        }
        
        $this->em->commit();

        return $weborders;
    }

    public function get($orderNumber) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' AND order = '{$orderNumber}'", "*"
        );
                
        if (sizeof($response) == 0) {
            return null;
        }
                
        $repository = $this->em->getRepository('AppBundle:Invoice');
                
        $weborders = array();
        
        $this->em->beginTransaction();                

        foreach ($response as $item) {
            $weborder = $repository->findOrUpdate(array(
                'orderNumber' => $item->order,
                'customerNumber' => $item->customer,
                'status' => $item->stat,
                'invoiceDate' => new DateTime($item->invc_date)
            ));
            $weborders[] = $weborder;
        }
        
        $this->em->commit();

        return $weborders[0];
    }

}
