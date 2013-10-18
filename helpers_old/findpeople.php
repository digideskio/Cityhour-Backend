<?php

$data = file_get_contents("php://input");
$data = json_decode($data,true);

// Debug ?
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) {
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
}
else {
    error_reporting(0);
}

include_once 'FindPeople.class.php';

if (isset($data["map"]) && $data["map"]) {
    new FindPeople($debug,true,$data);
}
else {
    new FindPeople($debug,false,$data);
}


if ($debug) {
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    echo 'Page generated in '.$total_time.' seconds.';
}