<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$server = "http://wt-distone01:8080";
$username = "webserver";
$password = "Webserver1Password";
$grantToken = "";
$accessToken = "";

// grant
echo "Starting Grant...";
$start = time();
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $server . "/distone/rest/service/authorize/grant");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    'client' => 'com.williamstradingco.app',
    'company' => 'wtc',
    'username' => $username,
    'password' => $password
)));

$response = json_decode(curl_exec($ch));

$grantToken = $response->grant_token;
$accessToken = $response->access_token;

curl_close($ch);
echo " it took: " . (time() - $start) . "\n";

// authorize
echo "Starting Authorize...";
$start = time();
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $server . "/distone/rest/service/authorize/access");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    'client' => 'com.williamstradingco.app',
    'company' => 'wtc',
    'grant_token' => $grantToken
)));

$response = json_decode(curl_exec($ch));

$accessToken = $response->access_token;

curl_close($ch);
echo " it took: " . (time() - $start) . "\n";

// query
echo "Starting Query...";
$start = time();

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $server . "/distone/rest/service/data/read");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: ' . $accessToken
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    'query' => "FOR EACH oe_head NO-LOCK WHERE company_oe = 'WTC' AND rec_type = 'O' USE-INDEX customer_date_d",
    'columns' => "*",
    'skip' => 0,
    'take' => 50
)));

$response = json_decode(curl_exec($ch));

curl_close($ch);

foreach ($response as $item) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $server . "/distone/rest/service/data/read");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: ' . $accessToken
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'query' => "FOR EACH oe_status NO-LOCK WHERE company_oe = 'WTC' AND order = '{$item->order}'",
        'columns' => "*",
        'skip' => 0,
        'take' => 50
    )));

    $response2 = json_decode(curl_exec($ch));

    curl_close($ch);
    
}

echo " it took: " . (time() - $start) . " to retrieve " . sizeof($response) . " records\n";
