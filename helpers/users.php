<?php
require 'classes/Users.class.php';

//$data = '
//    {
//        "private_key": "2",
//        "debug": true,
//        "data_to": 1389207600,
//        "time_to": 1389207600,
//        "time_from": 1389204000,
//        "offset": -18000,
//        "data_from": 1389204000,
//        "lat": 40.7144,
//        "lng": -74.006,
//        "data": [{"id":"356","user_id":"27"},{"id":"205","user_id":"19"},{"id":"227","user_id":"20"},{"id":"167","user_id":"17"},{"id":"49","user_id":"1007"},{"id":"179","user_id":"18"},{"id":"34","user_id":"1006"},{"id":"1","user_id":"1000"},{"id":"287","user_id":"24"},{"id":"284","user_id":"23"},{"id":"252","user_id":"22"},{"id":"239","user_id":"21"}]
//    }
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