<?php

namespace AppBundle\Service;

use AppBundle\Entity\Credit;
use AppBundle\Entity\CreditItem;
use AppBundle\Entity\ErpItem;
use AppBundle\Entity\ErpOrder;
use AppBundle\Entity\ErpPackage;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceItem;
use AppBundle\Entity\Package;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\SalesOrderItem;
use AppBundle\Entity\Shipment;
use AppBundle\Entity\ShipmentItem;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use SoapClient;
use SoapFault;
use Symfony\Component\Console\Output\OutputInterface;

class ErpOrderSyncService {

    /**
     *
     * @var EntityManager
     */
    private $_em;

    /**
     *
     * @var ErpOneConnectorService
     */
    private $_erp;
    private $_wsdlLocation;
    private $_soapClient;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $wsdlLocation) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_wsdlLocation = $wsdlLocation;

        $this->_soapClient = new SoapClient($this->_wsdlLocation, array(
            'login' => "admin",
            'password' => "test",
            'cache_wsdl' => WSDL_CACHE_NONE));
    }

    public function updateOpenOrders(OutputInterface $output) {

        $orders = $this->_em->getRepository('AppBundle:SalesOrder')->findBy(array('open' => true));

        foreach ($orders as $order) {
            try {
                $this->_soapClient->updateSalesOrder($order->getOrderNumber());
            } catch (SoapFault $fault) {
                $output->writeln("Couldn't submit webservice call " + $fault->getMessage());
            }
        }
    }

    public function loadNewOrders(OutputInterface $output) {

        $lastKnownOrder = $this->_em->createQuery("SELECT o.orderNumber FROM AppBundle:SalesOrder o ORDER BY o.orderNumber DESC")->setMaxResults(1)->getSingleScalarResult();

        if ($lastKnownOrder !== null) {

            $query = "FOR EACH oe_head NO-LOCK WHERE "
                    . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_head.rec_type = 'O' "
                    . "AND oe_head.order > '{$lastKnownOrder}' ";
        } else {

            $query = "FOR EACH oe_head NO-LOCK WHERE "
                    . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_head.rec_type = 'O' "
                    . "AND oe_head.opn = yes ";
        }

        $fields = "oe_head.order";

        $batch = 0;
        $batchSize = 1000;

        do {

            $result = $this->_erp->read($query, $fields, $batch, $batchSize);
           
            foreach ($result as $item) {
                try {
                    $this->_soapClient->updateSalesOrder($item->oe_head_order);
                } catch (SoapFault $fault) {
                    $output->writeln("Couldn't submit webservice call " + $fault->getMessage());
                }
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        } while (!empty($result));
    }

}
