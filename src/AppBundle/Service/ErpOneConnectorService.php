<?php

namespace AppBundle\Service;

class ErpOneConnectorService {    
    
    private $_grantToken;
    private $_accessToken;
    private $_server;
    private $_username;
    private $_password;
    private $_ch;
    
    public function __destruct() {
        curl_close($this->_ch);
    }
    
    public function __construct($server, $username, $password) {
        
        $this->_server = $server;
        $this->_username = $username;
        $this->_password = $password;

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->_server . "/distone/rest/service/authorize/grant");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'client' => 'com.williamstradingco.app',
            'company' => 'wtc',
            'username' => $this->_username,
            'password' => $this->_password
        )));
        
        $response = json_decode(curl_exec($ch));
        
        $this->_grantToken = $response->grant_token;
        $this->_accessToken = $response->access_token;
        
        $this->_ch = $ch;
        
    }    
    
    private function _refreshToken() {

        $ch = $this->_ch;
        
        curl_setopt($ch, CURLOPT_URL, $this->_server . "/distone/rest/service/authorize/access");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'client' => 'com.williamstradingco.app',
            'company' => 'wtc',
            'grant_token' => $this->_grantToken
        )));
        
        $response = json_decode(curl_exec($ch));
        
        $this->_accessToken = $response->access_token;
        
        
    }
    
    public function read($query, $columns = "*", $offset = 0, $limit = 0) {
        
        $this->_refreshToken();

        $ch = $this->_ch;
        
        curl_setopt($ch, CURLOPT_URL, $this->_server . "/distone/rest/service/data/read");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: ' . $this->_accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'query' => $query,
            'columns' => $columns,
            'skip' => $offset,
            'take' => $limit
        )));
        
        $response = json_decode(curl_exec($ch));
        
        return $response;
        
    }
    
    /**
     * Gets item pricing based on customer, quantity, and unit of measure
     * 
     * Returns a object containing the following:
     * 
     * item - Item Number of the item that the price was calculated for.
     * warehouse - Warehouse Code used in the price calculation.
     * customer - Customer Id used to calculate customer based pricing.
     * cu_group - Customer Group code used in the price calculation.
     * vendor - Vendor Id used in the price calculation.
     * quantity - Quantity used to get the price at a specific quantity break level.
     * price - The calculated price of the item.
     * unit - Unit of measure code (price per).
     * origin - Price calculation origin code. This code indicates how the price was calculated internally.
     * commission - A sales commission percentage for the item.
     * column - Column price label when a column price was used in the calculation.
     * 
     * @param string $itemNumber
     * @param string $customer
     * @param integer $quantity
     * @param string $uom
     * @return object
     */
    public function getItemPriceDetails($itemNumber, $customer = null, $quantity = 1, $uom = "EA") {
        
        $this->_refreshToken();

        $ch = $this->_ch;
        
        $queryData = array();
        
        $queryData['item'] = $itemNumber;
        
        if ($customer !== null) {
            $queryData['customer'] = $customer;
        }
        
        $queryData['quantity'] = $quantity;
        $queryData['unit'] = $uom;
        
        $query = http_build_query($queryData);
                
        curl_setopt($ch, CURLOPT_URL, $this->_server . "/distone/rest/service/price/fetch?" . $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->_accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = json_decode(curl_exec($ch));
        
        return $response;
        
    }
    
}