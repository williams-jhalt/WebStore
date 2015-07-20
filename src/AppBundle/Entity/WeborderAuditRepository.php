<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeborderAuditRepository extends EntityRepository {

    public function findOrCreate(array $data) {

        $audit = $this->findOneBy(array(
            'orderNumber' => $data['orderNumber'],
            'timestamp' => $data['timestamp']
        ));

        if (!$audit) {
            $audit = new WeborderAudit();
            $audit->setWeborder($data['weborder']);
            $audit->setOrderNumber($data['orderNumber']);
            $audit->setComment($data['comment']);
            $audit->setRecordType($data['recordType']);
            $audit->setStatusCode($data['statusCode']);
            $audit->setTimestamp($data['timestamp']);
            $this->getEntityManager()->persist($audit);
            $this->getEntityManager()->flush();
        }

        return $audit;
    }

}
