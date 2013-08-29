<?php

$data = file_get_contents("php://input");
$data = json_decode($data,true);

// Debug ?
if (isset($data["debug"]) && $data["debug"]) {
    $debug = true;
}
else {
    $debug = false;
}

if ($debug) {
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
}
else {
    error_reporting(0);
}

include_once 'FreeSlots.class.php';

new FreeSlots($debug,$data);

if ($debug) {
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    echo 'Page generated in '.$total_time.' seconds.';
}








