<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class WeborderAuditService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
    }

    /**
     * @param string $orderNumber
     * @return array
     */
    public function findByOrderNumber($orderNumber) {

        $repository = $this->_em->getRepository('AppBundle:WeborderAudit');
        $weborderRep = $this->_em->getRepository('AppBundle:Weborder');
        $weborder = $weborderRep->findOneBy(array('orderNumber' => $orderNumber));

        $items = array();

        $response = $this->_erp->read("FOR EACH oe_status WHERE company_oe = 'WTC' AND order = '{$orderNumber}'");

        $this->_em->beginTransaction();

        foreach ($response as $item) {

            $items[] = $repository->findOrCreate(array(
                'weborder' => $weborder,
                'recordDate' => $item->stat_date,
                'recordTime' => $item->stat_ttime,
                'orderNumber' => $item->order,
                'comment' => $item->comment,
                'recordType' => $item->rec_type,
                'statusCode' => $item->stat
            ));
        }

        $this->_em->commit();

        return $items;
    }

}
