<?php

namespace AppBundle\Service;

use AppBundle\Entity\WeborderAudit;
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

        $weborderRep = $this->_em->getRepository('AppBundle:Weborder');
        $weborder = $weborderRep->findOneBy(array('orderNumber' => $orderNumber));

        $items = array();

        $response = $this->_erp->read("FOR EACH oe_status WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$orderNumber}'");

        foreach ($response as $item) {

            $items[] = new WeborderAudit(array(
                'weborder' => $weborder,
                'recordDate' => $item->stat_date,
                'recordTime' => $item->stat_ttime,
                'orderNumber' => $item->order,
                'comment' => $item->comment,
                'recordType' => $item->rec_type,
                'statusCode' => $item->stat
            ));
        }

        return $items;
    }

}
