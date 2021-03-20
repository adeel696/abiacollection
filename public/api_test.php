<?php

echo $msisdn = '2348099444309';
//Charge user
$date = DateTime::createFromFormat('U.u', microtime(TRUE));
$session =  $date->format('YmdHisu');
$data = array(
	"serviceName" => "ITRVL10",
	"msisdn" => "08".substr($msisdn,-9),
	"id" => "ITRVL10-".$session,
	"amount" => "1000"
);
//print_r($data);
$payload = json_encode($data);
 
// Prepare new cURL resource
$ch = curl_init('https://directbill.9mobile.com.ng/sync/');

curl_setopt($ch, CURLOPT_HEADER      ,0);  // DO NOT RETURN HTTP HEADERS
curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

//needed for https
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
 
// Set HTTP Header for POST request 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	"Accept: application/json",
	"Content-Type: application/json",
	"username: IYCONSOFT",
	"Authorization: 9AFF9647F037D4E80A461A4B4A9656565FC50FCC2D5BF551A0444EDB4CACEDBF",
	"Ocp-Apim-Subscription-Key: ddbb55e046904b1eb5886622d24d3670"
	)
);
 
// Submit the POST request
$result = curl_exec($ch);
 
// Close cURL session handle
curl_close($ch);
$result = json_decode($result, true);
print_r($result);