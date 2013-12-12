<?php
require 'classes/Users.class.php';

//$data = '
//{
//    "private_key": "31934776312e9458f5eeb61a36c6227ea3b7a60b52a9823aaa4c6",
//    "debug": true,
//    "data_to": 1386892800,
//    "time_to": 1386738000,
//    "time_from": 1386820800,
//    "offset": -18000,
//    "data_from": 1386892800,
//    "lat": 40.723779,
//    "lng": -73.99128899999999,
//      "data": [
//      {
//        "id": "92",
//        "user_id": "4111"
//      }
//    ]
//}
//';


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;
if ($debug) require_once '../vendor/ref/ref.php';

$cls = new Users($debug);

$cls->connect();
$cls->getValues($data);

$slots = array();
$first = array();
$users = array();
$i = 0;

foreach ($cls->ids as $row) {
    if (isset($row['user_id']) && is_numeric($row['user_id']) && isset($row['id']) && is_numeric($row['id'])) {
        if ($i < 25) {
            array_push($first,$row['id']);
        }
        else {
            array_push($slots,$row);
        }

        if (!in_array($row['user_id'],$users)) {
            $i++;
            array_push($users,$row['user_id']);
        }
    }
}

if ($first) {
    $users = $cls->getUsers($first);
    if (!$users && is_numeric($cls->lat) && is_numeric($cls->lng)) {
        $cls->getMoreTime();
        $users = $cls->getUsers($first);
    }

    $cls->answer(array(
        'users' => $users,
        'data' => $slots
    ),200);
}
else {
    $cls->answer('Not all params given',410);
}