<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PackageRepository extends EntityRepository {

    public function findOrCreate(array $data) {

        $package = $this->findOneBy(array(
            'orderNumber' => $data['orderNumber'],
            'trackingNumber' => $data['trackingNumber']
        ));

        if (!$package) {
            $package = new Package();

            $package->setOrderNumber($data['orderNumber']);
            $package->setTrackingNumber($data['trackingNumber']);
            $package->setPackageCharge($data['packageCharge']);

            $this->getEntityManager()->persist($package);
            $this->getEntityManager()->flush();
        }

        return $package;
    }

}
