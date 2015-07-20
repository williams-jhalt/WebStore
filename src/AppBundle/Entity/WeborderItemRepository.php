<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeborderItemRepository extends EntityRepository {

    public function findOrCreate(array $data) {

        $item = $this->findOneBy(array(
            'orderNumber' => $data['orderNumber'],
            'sku' => $data['sku']
        ));

        if (!$item) {
            $item = new WeborderItem();
            $item->setWeborder($data['weborder']);
            $item->setOrderNumber($data['orderNumber']);
            $item->setSku($data['sku']);
            $item->setQuantity($data['quantity']);
            $this->getEntityManager()->persist($item);
            $this->getEntityManager()->flush();
        }

        return $item;
    }

}
