<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CustomerRepository extends EntityRepository {

    public function findOrUpdate(array $data) {
        
        $customer = $this->findOneBy(array('customerNumber' => $data['customerNumber']));
        
        if (!$customer) {
            $customer = new Customer();
        }

        $customer->setCustomerNumber($data['customerNumber']);
        
        $this->getEntityManager()->persist($customer);
        $this->getEntityManager()->flush();

        return $customer;
    }

}
