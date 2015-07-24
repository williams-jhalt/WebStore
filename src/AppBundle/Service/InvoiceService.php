<?php

namespace AppBundle\Service;

use AppBundle\Service\Soap\Weborder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\DateTime;

class InvoiceService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
    }

    public function _loadFromErp($item) {

        $weborderRep = $this->em->getRepository('AppBundle:Weborder');
        $weborder = $weborderRep->findOneByOrderNumber($item->order);

        return new Weborder(array(
            'weborder' => $weborder,
            'orderNumber' => $item->order,
            'customerNumber' => $item->customer,
            'status' => $item->stat,
            'invoiceDate' => new DateTime($item->invc_date)
        ));
    }

    public function findAll($offset = 0, $limit = 100) {


        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }

        return $weborders;
    }

    public function findByCustomer($customerNumber, $offset = 0, $limit = 100) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' AND customer = '{$customerNumber}' BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }

        return $weborders;
    }

    public function findByCustomerNumbers(array $customerNumbers, $offset = 0, $limit = 100) {

        $customerSelect = "";
        for ($i = 0; $i < count($customerNumbers); $i++) {
            $customerSelect .= " customer = '{$customerNumbers[$i]}' ";
            if ($i < count($customerNumbers) - 1) {
                $customerSelect .= " OR ";
            }
        }

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK "
                . "WHERE company_oe = 'WTC' "
                . "AND rec_type = 'I' "
                . "AND ({$customerSelect}) "
                . "BY oe_head.order DESCENDING", "*", $offset, $limit
        );

        $weborders = array();

        foreach ($response as $item) {
            $weborders[] = $this->_loadFromErp($item);
        }

        return $weborders;
    }

    public function get($orderNumber) {

        $response = $this->erp->read(
                "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'I' AND order = '{$orderNumber}'", "*"
        );

        if (sizeof($response) == 0) {
            return null;
        }

        return $this->_loadFromErp($response[0]);
    }

}
