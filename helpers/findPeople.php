<?php

//$data = '
//{
//   "debug": true,
//    "private_key": "383b0de8fd53460e514905f23e865e894550e7f352e8e01a56344",
//    "data_to": 1392681600,
//    "city": "CjQuAAAA5Jlo7RUa1GksfA7tN28wBpF34fZT3v7wY8D7nR15Fqm8z6NZx6vHd5Ufp1Qz8U3VEhByHuvBMFf1GnUVFg2sm45xGhQAF3Ee6KMTEN_xIzSYpAb9ztyXIw",
//    "time_to": 1392768000,
//    "time_from": 1392660000,
//    "offset": 0,
//    "data_from": 1392681600
//}
//';



$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/digitalnature/php-ref/ref.php';
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
