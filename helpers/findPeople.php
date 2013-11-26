<?php

//$data = '
//{
//   "private_key": "1001",
//   "debug": true,
//    "data_to": 1385683200,
//    "time_to": 1385611200,
//    "time_from":  1385431200,
//    "data_from": 1385683200,
//    "offset": -28800,
//   "goal": null,
//   "city": "CkQ4AAAA6sHuIwMgEPiQygMt7ibp7MT7Z8Uum30tUS96g3g1zGPOjVLrAg18qU3jZRvYpiUfRYn46UlvNcNMXYMDu3VcDxIQta9uo-ahZbIJ-oAqVnU7BRoUQ3GGOIdb9gOdu7j8laCfihgMhu4"
//}
//';



$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
require 'classes/FindPeople.class.php';
$cls = new FindPeople($debug);

$cls->connect();
$cls->getValues($data);

if ($result = $cls->findUsers()) {
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
}
else {
	$cls->answer('No one found.',410);
}
