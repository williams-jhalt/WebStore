<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductAttachmentRepository extends EntityRepository {

    public function findOrCreate(array $parameters) {
        $entity = $this->findOneBy(array('path' => $parameters['path']));

        if (null === $entity) {
            $entity = new ProductAttachment();
            $entity->setProduct($parameters['product']);
            $entity->setPath($product['path']);
            $entity->setExplicit($product['explicit']);
            $this->_em->persist($entity);
            $this->_em->flush();
        }

        return $entity;
    }

}
