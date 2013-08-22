<?php


// Debug ?
$debug = false;


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

if (isset($_POST["map"]) && $_POST["map"]) {
    new FindPeople($debug,true);
}
else {
    new FindPeople($debug,false);
}


if ($debug) {
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    echo 'Page generated in '.$total_time.' seconds.';
}