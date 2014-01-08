<?php

// $data = '
//    {
//        "private_key": "31934776312e9458f5eeb61a36c6227ea3b7a60b52a9823aaa4c6",
//        "debug": true,
//        "data_to": 1389207600,
//        "time_to": 1389207600,
//        "time_from": 1389204000,
//        "offset": -18000,
//        "data_from": 1389204000,
//        "lat": 40.7144,
//        "lng": -74.006
//    }
// ';


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
require 'classes/Suggest.class.php';
$cls = new Suggest($debug);

$cls->connect();
if ($cls->getValues($data)) {
    $result = $cls->findUsers();
}
else {
    $now = time();
    $f = $now + $cls->offset;
    $e = $now + 172800 + $cls->offset;
    $data['data_from'] = $f;
    $data['time_from'] = $f;
    $data['data_to'] = $e;
    $data['time_to'] = $e;
    $cls->getValues($data);
    $result = $cls->findUsers();
}


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
$cls->answer(array(
    'users' => $cls->getUsers($first),
    'data' => $slots,
    'count' => $cls->countUsers(array_merge($first,$count))
),200);