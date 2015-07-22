<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WeborderAuditRepository extends EntityRepository {

    public function findOrCreate(array $data) {

        $audit = $this->findOneBy(array(
            'orderNumber' => $data['orderNumber'],
            'recordDate' => $data['recordDate'],
            'recordTime' => $data['recordTime']
        ));

        if (!$audit) {
            $audit = new WeborderAudit();
            $audit->setWeborder($data['weborder']);
            $audit->setOrderNumber($data['orderNumber']);
            $audit->setRecordDate($data['recordDate']);
            $audit->setRecordTime($data['recordTime']);
            $audit->setComment($data['comment']);
            $audit->setRecordType($data['recordType']);
            $audit->setStatusCode($data['statusCode']);
            $this->getEntityManager()->persist($audit);
            $this->getEntityManager()->flush();
        }

        return $audit;
    }

}
