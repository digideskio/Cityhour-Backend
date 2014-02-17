<?php

//$data = '
//{
//    "debug": true,
//    "private_key": "383b0de8fd53460e514905f23e865e894550e7f352e8e01a56344",
//    "data_to": 1393027200,
//    "time_to": 1392786000,
//    "time_from": 1392613200,
//    "offset": -18000,
//    "data_from": 1392595200,
//    "lat": 42.6289495,
//    "lng": -78.7375289
//}
//';


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
require 'classes/Suggest.class.php';
$cls = new Suggest($debug);

$cls->connect();

$now = time();
$f = $now + $cls->offset;
$e = $now + 172800 + $cls->offset;
$data['data_from'] = $f;
$data['time_from'] = $f;
$data['data_to'] = $e;
$data['time_to'] = $e;
$bad_time = true;
$cls->getValues($data);
$result = $cls->findUsers();

// OLD
//if ($cls->getValues($data)) {
//    $result = $cls->findUsers();
//    $bad_time = false;
//}
//else {
//    $now = time();
//    $f = $now + $cls->offset;
//    $e = $now + 172800 + $cls->offset;
//    $data['data_from'] = $f;
//    $data['time_from'] = $f;
//    $data['data_to'] = $e;
//    $data['time_to'] = $e;
//    $bad_time = true;
//    $cls->getValues($data);
//    $result = $cls->findUsers();
//}


// Get slots
$slots = array();
$first = array();
$users = array();
$count = array();
$i = 0;
$enough = 0;

foreach ($result as $row) {
    if (!in_array($row['user_id'],$users)) {
        $i++;
        array_push($users,$row['user_id']);

        if ($i < 20) {
            array_push($first,$row['id']);
        }
        else {
            unset($row['start_time']);
            unset($row['end_time']);
            unset($row['dist']);
            array_push($slots,$row);
            array_push($count,$row['id']);
        }

        if ($enough > 500) {
            break;
        }
        $enough++;
    }
}

$slots[] = array(
    'lat' => $cls->lat,
    'lng' => $cls->lng,
    'bad_time' => $bad_time,
);
$cls->answer(array(
    'users' => $cls->getUsers($first),
    'data' => $slots,
    'count' => count($users)
),200);