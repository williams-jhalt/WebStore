<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class InvoiceRepository extends EntityRepository {

    public function findOrUpdate(array $data) {
        
        $invoice = $this->findOneBy(array('orderNumber' => $data['orderNumber']));
        
        if (!$invoice) {
            $invoice = new Invoice();
        }

        $invoice->setOrderNumber($data['orderNumber']);
        $invoice->setCustomerNumber($data['customerNumber']);
        $invoice->setInvoiceDate($data['invoiceDate']);
        $invoice->setStatus($data['status']);
        
        $this->getEntityManager()->persist($invoice);
        $this->getEntityManager()->flush();

        return $invoice;
    }

}
