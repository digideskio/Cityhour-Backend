<?php
require 'classes/Users.class.php';

//$data = '
//    {
//        "private_key": "df9786cd92ab23c72c1ef7e21368991cbfcffd5452efa8e8cc5bf",
//        "debug": true,
//        "data_to": 1391644800,
//        "time_to": 1391464800,
//        "time_from": 1391547600,
//        "offset": 7200,
//        "data_from": 1391644800,
//        "data": [
//        {"id":"286","user_id":"24"},
//        {"id":"263","user_id":"22"},
//        {"id":"237","user_id":"21"},
//        {"id":"102","user_id":"13"},
//        {"id":"109","user_id":"14"},
//        {"id":"179","user_id":"18"},
//        {"id":"156","user_id":"16"},
//        {"id":"126","user_id":"15"},
//        {"id":"515","user_id":"9"},
//        {"id":"19","user_id":"10"},
//        {"id":"327","user_id":"26"},
//        {"id":"320","user_id":"25"},
//        {"lat":"40.7144","lng":"-74.006"}
//        ]
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
    elseif (isset($row['lat']) && isset($row['lng']) && is_numeric($row['lat']) && is_numeric($row['lng'])) {
        $cls->lat = $row['lat'];
        $cls->lng = $row['lng'];
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