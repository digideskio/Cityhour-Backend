<?php

// $data = '
// {
//      "private_key": "42530ec9ae353de65dbc386b8d9bd9449ac99b845231c239d9c6a",
//      "debug": true,
//      "data_from": "1383672800",
//      "data_to": "1384339600",
//      "time_from": "1382662800",
//      "time_to": "1383339600",
//      "lat": "50.4362640380859400",
//      "lng": "30.5156993865966800"
// }
// ';


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/Suggest.class.php';
$cls = new Suggest($debug);

$cls->connect();
$cls->getValues($data);

$result = $cls->findUsers();
if (!$result) {
    $now = time();
    $f = $now - 43200;
    $e = $now + 43200;
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

        if ($i < 25) {
            array_push($first,$row['id']);
        }
        else {
            unset($row['start_time']);
            unset($row['end_time']);
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