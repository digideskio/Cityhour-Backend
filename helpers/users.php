<?php

// Debug ?
$debug = false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/UpdateOne.class.php';
$cls = new UpdateOne($debug);


if (isset($_GET['ids']) && is_numeric($_GET['ids']) && isset($_GET['private_key']) && $_GET['private_key']) {
    $ids = $_GET['ids'];
    $private_key = $_GET['private_key'];
}
else {
    $cls->answer('Not all params given',400);
}

$cls->connect();
$cls->getUser($private_key);
$ids = explode(',',$ids);
$clean_ids = array();
$i = 0;

foreach ($ids as $row) {
    if (is_numeric($row)) {
        array_push($clean_ids,$row);
    }
    if ($i > 25) {
        break;
    }
    $i++;
}

if ($clean_ids) {
    $cls->answer($cls->getUsers($ids),200);
}
else {
    $cls->answer('Not all params given',400);
}