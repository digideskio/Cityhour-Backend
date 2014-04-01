<?php

// Debug ?
$debug = false;

if ($debug) require_once '../vendor/ref/ref.php';

require 'classes/UpdateAll.class.php';

$cls = new UpdateAll($debug);
$cls->connect();

$AllData = $cls->getAllData();
$magicSlots = $cls->makeMagic($AllData);

if ($cls->storeMagic($magicSlots))
    $cls->answer('Done',200);
else
    $cls->answer('Server error',500);