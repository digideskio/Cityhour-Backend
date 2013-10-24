<?php

// Debug ?
$debug = false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/UpdateOne.class.php';
$cls = new UpdateOne($debug);


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
}
else {
    $cls->answer('Not all params given',400);
}

$cls->connect();
$AllData = $cls->getAllData($id);

if (isset($AllData[0])) {
    $magicSlots = $cls->makeMagic($AllData);
    if ($cls->storeOneMagic($magicSlots,$id))
        $cls->answer('Done',200);
    else
        $cls->answer('Server error',500);
}
else {
    $cls->answer('Done',200);
}