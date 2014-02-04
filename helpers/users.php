<?php
require 'classes/Users.class.php';

//$data = '
//{
//    "private_key": "df9786cd92ab23c72c1ef7e21368991cbfcffd5452efa8e8cc5bf",
//    "data_to": 1391731200,
//    "time_to": 1391490000,
//    "data": [
//        {
//            "id": "137",
//            "user_id": "15"
//        },
//        {
//            "id": "341",
//            "user_id": "27"
//        },
//        {
//            "id": "372",
//            "user_id": "28"
//        },
//        {
//            "id": "200",
//            "user_id": "19"
//        },
//        {
//            "id": "215",
//            "user_id": "20"
//        },
//        {
//            "id": "36",
//            "user_id": "1006"
//        },
//        {
//            "id": "169",
//            "user_id": "17"
//        },
//        {
//            "id": "179",
//            "user_id": "18"
//        },
//        {
//            "id": "48",
//            "user_id": "1007"
//        },
//        {
//            "id": "261",
//            "user_id": "22"
//        },
//        {
//            "id": "237",
//            "user_id": "21"
//        },
//        {
//            "lat": 40.723779,
//            "lng": -73.991289
//        }
//    ],
//    "time_from": 1391572800,
//    "offset": -18000,
//    "data_from": 1391472000
//}
//';


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;
if ($debug) require_once '../vendor/ref/ref.php';

$cls = new Users($debug);

$cls->connect();
$cls->getValues($data);

$bad_time = false;
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

        $bad_time =  (isset($row['bad_time']) && $row['bad_time']) ? true:false;
    }
}

if ($first) {
    if ($bad_time) {
        $cls->getMoreTime();
    }


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