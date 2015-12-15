<?php

namespace AppBundle\Service;

use AppBundle\Soap\SoapManufacturer;
use AppBundle\Soap\SoapProduct;
use AppBundle\Soap\SoapProductAttachment;
use AppBundle\Soap\SoapProductDetail;
use AppBundle\Soap\SoapProductType;
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
    private $_wholesaleUrl = "http://wholesale.williams-trading.com/rest";

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

    public function loadFromWholesale(OutputInterface $output) {

        $ch = curl_init();

        $headers = get_headers($this->_wholesaleUrl . "/products?format=json", 1);

        $matches = array();

        preg_match("/items [0-9]+-[0-9]+\/([0-9]+)/", $headers['X-Content-Range'], $matches);

        $offset = 0;
        $limit = 100;
        $total = $matches[1];
        $next = $limit;

        while ($offset < $total) {

            $productData = array();

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Range: $offset-$next"));
            curl_setopt($ch, CURLOPT_URL, $this->_wholesaleUrl . "/products?format=json");
            $response = json_decode(curl_exec($ch));

            foreach ($response->products as $product) {
                $productData[$product->sku] = array(
                    'description' => $product->description,
                    'height' => $product->height,
                    'length' => $product->length,
                    'width' => $product->width,
                    'weight' => $product->weight,
                    'color' => $product->color,
                    'material' => $product->material,
                    'images' => array()
                );
            }

            $mh = curl_multi_init();
            $chs = array();

            foreach (array_keys($productData) as $sku) {

                $chx = curl_init();
                curl_setopt($chx, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($chx, CURLOPT_URL, $this->_wholesaleUrl . "/product-images/{$sku}?format=json");
                $chs[$sku] = $chx;
                curl_multi_add_handle($mh, $chx);
            }

            $active = null;

            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
            }

            foreach ($chs as $sku => $chx) {

                $response2 = json_decode(curl_multi_getcontent($chx));

                if (isset($response2->images)) {
                    foreach ($response2->images as $image) {
                        $productData[$sku]['images'][] = array(
                            'path' => $image->image_url,
                            'explicit' => $image->explicit,
                            'primary' => $image->primary
                        );
                    }
                }

                curl_multi_remove_handle($mh, $chx);
                curl_close($chx);
            }

            curl_multi_close($mh);

            $existingData = $this->_soapClient->findProducts(array('skus' => array_keys($productData)));

            $list = array();

            if (isset($existingData->products->product) && is_array($existingData->products->product)) {
                $list = $existingData->products->product;
            } else {
                $list = $existingData->products;
            }

            foreach ($list as $p) {
                $p->detail->textDescription = $productData[$p->sku]['description'];
                $p->detail->packageHeight = $productData[$p->sku]['height'];
                $p->detail->packageLength = $productData[$p->sku]['length'];
                $p->detail->packageWidth = $productData[$p->sku]['width'];
                $p->detail->packageWeight = $productData[$p->sku]['weight'];
                $p->detail->color = $productData[$p->sku]['color'];
                $p->detail->material = $productData[$p->sku]['material'];

                $p->attachments = array();

                foreach ($productData[$p->sku]['images'] as $i) {
                    $a = new SoapProductAttachment();
                    $a->path = $i['path'];
                    $a->explicit = $i['explicit'];
                    $a->primaryAttachment = $i['primary'];
                    $p->attachments[] = $a;
                }
            }

            try {
                $this->_soapClient->updateProducts(array('products' => $list));
            } catch (SoapFault $fault) {
                $output->writeln("REQUEST:\n" . $this->_soapClient->__getLastRequest());
                $output->writeln("Couldn't submit webservice call " . $fault->getMessage());
            }

            $output->writeln("Loaded {$limit} items, total {$next}");

            $offset += $limit;
            $next = $offset + $limit;
        }

        curl_close($ch);
    }

    public
            function loadFromErp(OutputInterface $output) {

        $query = "FOR EACH item NO-LOCK WHERE "
                . "item.company_it = '{$this->_erp->getCompany()}' AND item.web_item = yes, "
                . "EACH wa_item NO-LOCK WHERE "
                . "wa_item.company_it = item.company_it AND wa_item.item = item.item";

        $fields = "item.item,item.manufacturer,item.product_line,item.descr,item.date_added,wa_item.qty_oh,wa_item.list_price,item.upc1";

        $batch = 0;
        $batchSize = 1000;

        $manufacturers = array();
        foreach ($this->_soapClient->findAllManufacturers() as $x) {
            if (is_array($x->manufacturer)) {
                foreach ($x->manufacturer as $m) {
                    $manufacturers[$m->code] = $m;
                }
            } else {
                $manufacturers[$x->manufacturer->code] = $x->manufacturer;
            }
        }

        $types = array();
        foreach ($this->_soapClient->findAllProductTypes() as $x) {
            if (is_array($x->productType)) {
                foreach ($x->productType as $t) {
                    $types[$t->code] = $t;
                }
            } else {
                $types[$x->productType->code] = $x->productType;
            }
        }

        do {

            $result = $this->_erp->read($query, $fields, $batch, $batchSize);

            $skus = array();
            $productData = array();

            $products = array();

            foreach ($result as $item) {

                $releaseDate = new DateTime($item->item_date_added);

                $skus[] = $item->item_item;
                $productData[$item->item_item]['sku'] = $item->item_item;
                $productData[$item->item_item]['name'] = implode(" ", $item->item_descr);
                $productData[$item->item_item]['price'] = $item->wa_item_list_price;
                $productData[$item->item_item]['stockQuantity'] = $item->wa_item_qty_oh;
                $productData[$item->item_item]['releaseDate'] = $releaseDate->format('Y-m-d');
                $productData[$item->item_item]['barcode'] = $item->item_upc1;
                $productData[$item->item_item]['manufacturerCode'] = $item->item_manufacturer;
                $productData[$item->item_item]['productTypeCode'] = $item->item_product_line;
            }

            $existingData = $this->_soapClient->findProducts(array('skus' => $skus));

            $list = array();

            if (isset($existingData->products->product) && is_array($existingData->products->product)) {
                $list = $existingData->products->product;
            } else {
                $list = $existingData->products;
            }

            foreach ($list as $e) {

                $e->name = $productData[$e->sku]['name'];
                $e->price = $productData[$e->sku]['price'];
                $e->stockQuantity = $productData[$e->sku]['stockQuantity'];
                $e->releaseDate = $productData[$e->sku]['releaseDate'];
                $e->barcode = $productData[$e->sku]['barcode'];

                if (array_key_exists($productData[$e->sku]['manufacturerCode'], $manufacturers)) {
                    $e->manufacturer = clone $manufacturers[$productData[$e->sku]['manufacturerCode']];
                } else {
                    $newm = new SoapManufacturer();
                    $newm->code = $productData[$e->sku]['manufacturerCode'];
                    $newm->name = $productData[$e->sku]['manufacturerCode'];
                    $e->manufacturer = $newm;
                }

                if (array_key_exists($productData[$e->sku]['productTypeCode'], $types)) {
                    $e->productType = clone $types[$productData[$e->sku]['productTypeCode']];
                } else {
                    $newpt = new SoapProductType();
                    $newpt->code = $productData[$e->sku]['productTypeCode'];
                    $newpt->name = $productData[$e->sku]['productTypeCode'];
                    $e->productType = $newpt;
                }

                $products[] = $e;

                unset($productData[$e->sku]);
            }

            foreach ($productData as $d) {

                $p = new SoapProduct();
                $p->sku = $d['sku'];
                $p->name = $d['name'];
                $p->price = $d['price'];
                $p->stockQuantity = $d['stockQuantity'];
                $p->releaseDate = $d['releaseDate'];
                $p->barcode = $d['barcode'];

                if (array_key_exists($d['manufacturerCode'], $manufacturers)) {
                    $p->manufacturer = $manufacturers[$d['manufacturerCode']];
                } else {
                    $newm = new SoapManufacturer();
                    $newm->code = $d['manufacturerCode'];
                    $newm->name = $d['manufacturerCode'];
                    $p->manufacturer = $newm;
                }

                if (array_key_exists($d['productTypeCode'], $types)) {
                    $p->productType = $types[$d['productTypeCode']];
                } else {
                    $newpt = new SoapProductType();
                    $newpt->code = $d['productTypeCode'];
                    $newpt->name = $d['productTypeCode'];
                    $p->productType = $newpt;
                }

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
    }

}
