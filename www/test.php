<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.07.2017
 * Time: 11:01
 */

$username = 'VoyageDiler';
$userpassword = 'VoyageDiler';

$headers = array(
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($username.":".$userpassword)
);

$url = 'http://develop.techostan.kz:3298/Auth/GetToken';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);

$error = curl_error($ch);
var_dump($error);

curl_close($ch);

var_dump($result);


