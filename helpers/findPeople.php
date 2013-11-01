<?php

//   ,
//   "goal": 1,
//   "industry": 4
// $data = '
// {
//   "private_key": "c3077d5cd3efe0797fb516b3cb216b3d55242f425221f6a71b83c",
//   "debug": true,
//   "data_from": "1382662800",
//   "data_to": "1383339600",
//   "time_from": "1382662800",
//   "time_to": "1383339600",
//   "city": "CjQwAAAAH897OFbrjeYH1ckvtO-CVX0D9dunM_5kprcHoc84YKHpG4vJDaeUGJTLV8DwsjveEhDroDqGwmH07wrceET28fqiGhTDQYRBJx20UWSjGW_3gLPaOsF9wQ"
//
// }
// ';



$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/FindPeople.class.php';
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
        if ($i < 25) {
            array_push($first,$row['id']);
        }
        else {
            array_push($slots,$row);
            array_push($count,$row['id']);
        }
        if (!in_array($row['user_id'],$users)) {
            $i++;
            array_push($users,$row['user_id']);

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
