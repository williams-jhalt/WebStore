<?php

namespace AppBundle\Service;

use AppBundle\Entity\Weborder;
use AppBundle\Entity\WeborderItem;
use DateTime;
use Doctrine\ORM\EntityManager;

class Webservice {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getProduct($id) {

        return $this->em->getRepository('AppBundle:Product')->find($id);
    }

    public function getWeborder($id) {

        return $this->em->getRepository('AppBundle:Weborder')->find($id);
    }

    public function submitWeborder($order) {

        $weborder = new Weborder();
        $weborder->setCustomerNumber($order['customer']);
        $weborder->setOrderDate(new DateTime());
        $weborder->setReference1($order['reference1']);
        $weborder->setReference2($order['reference2']);
        $weborder->setReference3($order['reference3']);
        $weborder->setRush($order['rush']);
        $weborder->setShipToAddress1($order['ship']['address1']);
        $weborder->setShipToAddress2($order['ship']['address2']);
        $weborder->setShipToCity($order['ship']['city']);
        $weborder->setShipToCountry($order['ship']['country']);
        $weborder->setShipToEmail($order['ship']['email']);
        $weborder->setShipToFirstName($order['ship']['firstName']);
        $weborder->setShipToLastName($order['ship']['lastName']);
        $weborder->setShipToPhone($order['ship']['phone']);
        $weborder->setShipToState($order['ship']['state']);
        $weborder->setShipToZip($order['ship']['zip']);

        $items = array();

        foreach ($order['items'] as $item) {
            $weborderitem = new WeborderItem();
            $weborderitem->setSku($item['sku']);
            $weborderitem->setQuantity($item['quantity']);
            $items[] = $weborderitem;
        }

        $weborder->setItems($items);

        $this->em->persist($weborder);

        $this->em->flush();
    }

}
