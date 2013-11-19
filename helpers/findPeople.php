<?php

//$data = '
//    {
//        "private_key": "42530ec9ae353de65dbc386b8d9bd9449ac99b845231c239d9c6a",
//        "debug": true,
//        "data_to": 1385078400,
//        "time_to": 1384984800,
//        "time_from":  1384873200,
//        "data_from": 1384819200,
//        "offset": 7200,
//       "goal": null,
//       "city": "CjQwAAAADRVm3LP2MJiiUqyCzTak3UGrrcb6XLsoVNJJfmbuPzZ8jGIwq7puO87X0QTFGE7PEhDGXt1jrTvvNw_mTqtqKwmXGhRrDgdTKXVUjAS8zr0gsk4awH3NJg"
//    }
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
