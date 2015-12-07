<?php

namespace AppBundle\Service;

use AppBundle\Soap\SoapSalesOrder;
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

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $wsdlLocation, $soapUser, $soapPass) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_wsdlLocation = $wsdlLocation;

        $this->_soapClient = new SoapClient($this->_wsdlLocation, array(
            'login' => $soapUser,
            'password' => $soapPass,
            'cache_wsdl' => WSDL_CACHE_NONE));
    }

    public function updateOpenOrders(OutputInterface $output) {

        $orders = $this->_em->getRepository('AppBundle:SalesOrder')->findBy(array('open' => true));

        $salesOrders = array();

        foreach ($orders as $order) {
            $so = new SoapSalesOrder();
            $so->orderNumber = $order->getOrderNumber();
            $salesOrders[] = $so;
        }

        $batch = 0;
        $batchSize = 100;

        while ($batch < sizeof($salesOrders)) {

            try {
                $this->_soapClient->updateSalesOrders(array_slice($salesOrders, $batch, $batchSize));
            } catch (SoapFault $fault) {
                $output->writeln("Couldn't submit webservice call " + $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        };
    }

    public function loadNewOrders(OutputInterface $output) {

        $lastKnownOrder = $this->_em->getRepository('AppBundle:SalesOrder')->findOneBy(array(), array('orderNumber' => 'desc'));

        if ($lastKnownOrder !== null) {

            $query = "FOR EACH oe_head NO-LOCK WHERE "
                    . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_head.rec_type = 'O' "
                    . "AND oe_head.order > '{$lastKnownOrder->getOrderNumber()}' ";
        } else {

            $query = "FOR EACH oe_head NO-LOCK WHERE "
                    . "oe_head.company_oe = '{$this->_erp->getCompany()}' "
                    . "AND oe_head.rec_type = 'O' "
                    . "AND oe_head.opn = yes ";
        }

        $fields = "oe_head.order";

        $batch = 0;
        $batchSize = 100;

        do {

            $result = $this->_erp->read($query, $fields, $batch, $batchSize);

            $salesOrders = array();

            foreach ($result as $item) {
                $so = new SoapSalesOrder();
                $so->orderNumber = $item->oe_head_order;
                $salesOrders[] = $so;
            }

            try {
                $this->_soapClient->updateSalesOrders($salesOrders);
            } catch (SoapFault $fault) {
                $output->writeln("Couldn't submit webservice call " + $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        } while (!empty($result));
    }

}
