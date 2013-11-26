<?php

//$data = '
//{
//   "private_key": "1001",
//   "debug": true,
//    "data_to": 1385784000,
//    "time_to": 1385784000,
//    "time_from":  1385776800,
//    "data_from": 1385784000,
//    "offset": -28800,
//   "goal": null,
//   "city": "CkQ4AAAAtbNSTUezJnQ_n2Ix7rUWD5BXE1ZTEuGn6T2bHdxLqPSACshU5HsKtjimXrvWH4nnlwegoCnKN003KTQEbMfkKRIQUQ5utosl0XiTUHQWQPt28BoUEg0acNkfcb19iw2egDJwsbFi0hM"
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
