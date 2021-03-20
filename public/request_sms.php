<?php
$msisdn = $_GET['msisdn'];
$shortcode = $_GET['shortcode'];
$message = $_GET['message'];

$url = "http://localhost/itravel/public/sms.php?msisdn=$msisdn&from=$shortcode&keyword=$message";

echo file_get_contents($url);
