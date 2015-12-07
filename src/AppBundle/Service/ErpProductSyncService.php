<?php

namespace AppBundle\Service;

use AppBundle\Soap\SoapProduct;
use DateTime;
use Doctrine\ORM\EntityManager;
use SoapClient;
use SoapFault;
use Symfony\Component\Console\Output\OutputInterface;

class ErpProductSyncService {

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

    public function loadFromErp(OutputInterface $output) {

        $query = "FOR EACH item NO-LOCK WHERE "
                . "item.company_it = '{$this->_erp->getCompany()}' AND item.web_item = yes, "
                . "EACH wa_item NO-LOCK WHERE "
                . "wa_item.company_it = item.company_it AND wa_item.item = item.item";

        $fields = "item.item,item.manufacturer,item.product_line,item.descr,item.date_added,wa_item.qty_oh,wa_item.list_price";

        $batch = 0;
        $batchSize = 1000;

        do {

            $result = $this->_erp->read($query, $fields, $batch, $batchSize);

            $products = array();

            foreach ($result as $item) {

                $releaseDate = new DateTime($item->item_date_added);

                $p = new SoapProduct();
                $p->sku = $item->item_item;
                $p->name = implode(" ", $item->item_descr);
                $p->price = $item->wa_item_list_price;
                $p->stockQuantity = $item->wa_item_qty_oh;
                $p->manufacturerCode = $item->item_manufacturer;
                $p->productTypeCode = $item->item_product_line;
                $p->releaseDate = $releaseDate->format('Y-m-d');

                $products[] = $p;
            }

            $results = 0;

            try {
                $results = $this->_soapClient->updateProducts($products);
            } catch (SoapFault $fault) {
                $output->writeln("Couldn't submit webservice call " + $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batch} items, wrote {$results} to webservice");
        } while (!empty($result));
    }

}
