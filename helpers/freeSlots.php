<?php

//$data = '
// {
//   "private_key": "c3077d5cd3efe0797fb516b3cb216b3d55242f425221f6a71b83c",
//   "debug": true,
//   "user_id": "111"
// }
// ';



$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/FreeSlots.class.php';
$cls = new FreeSlots($debug);

$cls->connect();
$cls->getValues($data);
$cls->answer($cls->getFree(),200);