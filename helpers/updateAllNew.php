<?php

// Debug ?
$debug = true;

if ($debug) require_once '../vendor/ref/ref.php';

require 'classes/UpdateOne.class.php';

$cls = new UpdateOne($debug);
$cls->connect();

$users = $cls->query("select id from users where status = 0 order by id asc",false,true);

foreach ($users as $row) {
    $AllData = $cls->getAllData($row['id']);

    if (isset($AllData[0])) {
        $magicSlots = $cls->makeMagic($AllData);
        $cls->storeOneMagic($magicSlots,$row['id']);
    }
    else {
        $cls->clearUserData($row['id']);
    }
}

$cls->answer('Done',200);
