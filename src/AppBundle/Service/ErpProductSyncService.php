<?php

namespace AppBundle\Service;

use AppBundle\Soap\SoapProduct;
use AppBundle\Soap\SoapProductAttachment;
use AppBundle\Soap\SoapProductDetail;
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
            'keep_alive' => true,
            'trace' => 1));
    }

    public function loadFromErp(OutputInterface $output) {

        $query = "FOR EACH item NO-LOCK WHERE "
                . "item.company_it = '{$this->_erp->getCompany()}' AND item.web_item = yes, "
                . "EACH wa_item NO-LOCK WHERE "
                . "wa_item.company_it = item.company_it AND wa_item.item = item.item";

        $fields = "item.item,item.manufacturer,item.product_line,item.descr,item.date_added,wa_item.qty_oh,wa_item.list_price,item.upc1";

        $batch = 0;
        $batchSize = 100;
        
        $ch = curl_init();

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
                $p->barcode = $item->item_upc1;

                curl_setopt($ch, CURLOPT_URL, "http://wholesale.williams-trading.com/rest/product-images/{$item->item_item}?format=json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                $response = json_decode(curl_exec($ch));
                
                $p->attachments = array();
                
                foreach ($response->images as $image) {
                    $pa = new SoapProductAttachment();
                    $pa->path = $image->image_url;
                    $pa->explicit = $image->explicit;
                    $pa->primaryAttachment = $image->primary;
                    $p->attachments[] = $pa;
                }

                curl_setopt($ch, CURLOPT_URL, "http://wholesale.williams-trading.com/rest/products/{$item->item_item}?format=json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                $response2 = json_decode(curl_exec($ch));                
                
                $p->detail = new SoapProductDetail();
                $p->detail->textDescription = $response2->product->description;
                $p->detail->packageHeight = $response2->product->height;
                $p->detail->packageLength = $response2->product->length;
                $p->detail->packageWeight = $response2->product->weight;
                $p->detail->packageWidth = $response2->product->width;

                $products[] = $p;
            }

            try {
                $this->_soapClient->updateProducts(array('products' => $products));
            } catch (SoapFault $fault) {
                $output->writeln("REQUEST:\n" . $this->_soapClient->__getLastRequest());
                $output->writeln("Couldn't submit webservice call " . $fault->getMessage());
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batchSize} items, total {$batch}");
        } while (!empty($result));
        
        curl_close($ch);
    }

}
