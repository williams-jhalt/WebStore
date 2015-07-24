<?php

namespace AppBundle\Service;

use AppBundle\Entity\WeborderItem;
use Doctrine\ORM\EntityManager;

class WeborderItemService {

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

        $response = $this->_erp->read("FOR EACH oe_line WHERE company_oe = 'WTC' AND rec_type = 'O' AND order = '{$orderNumber}'");

        foreach ($response as $item) {
            $items[] = new WeborderItem(array(
                'weborder' => $weborder,
                'orderNumber' => $item->order,
                'lineNumber' => $item->line,
                'sku' => $item->item,
                'quantity' => $item->q_ord
            ));
        }

        return $items;
    }

}
