<?php

// $data = '
//    {
//      "private_key": "42530ec9ae353de65dbc386b8d9bd9449ac99b845231c239d9c6a",
//      "debug": true,
//      "offset": 10800,
//      "lat": "50.4362640380859400",
//      "lng": "30.5156993865966800"
//    }
// ';

$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/Map.class.php';
$cls = new Map($debug);

$cls->start();
$cls->connect();
$cls->getValues($data);

$cls->answer($cls->findUsers(),200);
