<?php

$debug = true;
if ($debug) require_once '../vendor/ref/ref.php';

include_once 'classes/UpdateAll.class.php';

$cls = new UpdateAll($debug);
$cls->connect();

$AllData = $cls->getAllData();


if ($debug) $com->stopTimer();