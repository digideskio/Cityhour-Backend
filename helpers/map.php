<?php

// $data = '
//    {
//      "private_key": "c3077d5cd3efe0797fb516b3cb216b3d55242f425221f6a71b83c",
//      "debug": true,
//      "offset": 7200,
//      "lat": "50.4362640380859400",
//      "lng": "30.5156993865966800"
//    }
// ';

$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
require 'classes/Map.class.php';
$cls = new Map($debug);

$cls->connect();
$cls->getValues($data);

$cls->answer($cls->findUsers(),200);
