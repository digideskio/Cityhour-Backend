<?php
include_once 'db.class.php';
include_once 'allStaff.class.php';
$staff = new allStaff();

//Connect DB
$db = new DB();
$db->connect();

// Insert Users for query
$db->insertUser(1,'Bad boy',4,2,1,0);
$db->insertUser(2,'Good girl',4,2,1,0);

// Query params
$day = '2015-09-09 ';
$query_s = $day.'10:00:00';
$query_e = $day.'20:00:00';
$query_goal = 2;
$query_industry = 4;
$url = "http://helpers.truebear.com/findpeople.php";



// Insert User Denis
$db->insertUser(3,'Denis',4,2,1,0);
// Insert Calendar Slots
$db->insertSlot(3,1,$day.'16:00:00',$day.'17:00:00',0,0,0);
$db->insertSlot(3,1,$day.'13:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(3,1,$day.'15:00:00',$day.'17:30:00',2,1,0);
$db->insertSlot(3,1,$day.'18:00:00',$day.'19:00:00',2,2,2);
$db->insertSlot(3,1,$day.'10:00:00',$day.'12:00:00',0,0,0);


// Insert User Sergey
$db->insertUser(4,'Sergey',4,2,0,0);
// Insert Calendar Slots
$db->insertSlot(4,1,$day.'16:00:00',$day.'17:00:00',0,0,0);
$db->insertSlot(4,1,$day.'10:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(4,1,$day.'15:00:00',$day.'17:30:00',2,1,0);
$db->insertSlot(4,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(4,1,$day.'08:00:00',$day.'13:00:00',2,1,0);


// Insert User Leonid
$db->insertUser(5,'Leonid',4,2,0,0);
// Insert Calendar Slots
$db->insertSlot(5,1,$day.'09:00:00',$day.'22:00:00',0,0,0);
$db->insertSlot(5,1,$day.'15:00:00',$day.'16:00:00',2,1,0);
$db->insertSlot(5,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(5,1,$day.'08:00:00',$day.'13:00:00',2,1,0);

// Insert User Slava
$db->insertUser(6,'Slava',4,2,1,0);


// Insert User Anton
$db->insertUser(7,'Anton',2,2,0,0);
// Insert Calendar Slots
$db->insertSlot(7,1,$day.'16:00:00',$day.'17:00:00',0,0,0);
$db->insertSlot(7,1,$day.'10:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(7,1,$day.'15:00:00',$day.'17:30:00',2,1,0);
$db->insertSlot(7,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(7,1,$day.'08:00:00',$day.'13:00:00',2,1,0);

// Insert User Egor
$db->insertUser(8,'Egor',2,2,0,0);


// Insert User Vitaly
$db->insertUser(9,'Vitaly',2,2,0,0);
// Insert Calendar Slots
$db->insertSlot(9,1,$day.'16:00:00',$day.'17:00:00',0,0,0);
$db->insertSlot(9,1,$day.'10:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(9,1,$day.'15:00:00',$day.'17:30:00',0,0,0);
$db->insertSlot(9,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(9,1,$day.'08:00:00',$day.'09:00:00',0,0,0);

// Insert User Selvestor
$db->insertUser(10,'Selvestor',2,2,1,0);
// Insert Calendar Slots
$db->insertSlot(10,1,$day.'08:45:00',$day.'09:45:00',0,0,0);
$db->insertSlot(10,1,$day.'10:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(10,1,$day.'15:45:00',$day.'19:30:00',0,0,0);
$db->insertSlot(10,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(10,1,$day.'16:00:00',$day.'17:00:00',2,1,0);


// Insert User Janik
$db->insertUser(11,'Janik',2,2,1,0);
// Insert Calendar Slots
$db->insertSlot(11,1,$day.'08:45:00',$day.'09:45:00',0,0,0);
$db->insertSlot(11,1,$day.'10:00:00',$day.'14:45:00',0,0,0);
$db->insertSlot(11,1,$day.'15:45:00',$day.'19:30:00',0,0,0);
$db->insertSlot(11,1,$day.'12:00:00',$day.'13:00:00',2,2,2);
$db->insertSlot(11,1,$day.'16:00:00',$day.'17:00:00',3,1,0);


// Check
$result = $staff->request($url, array(
    'private_key' => '2',
    'data_from' => strtotime($query_s),
    'data_to' => strtotime($query_e),
    'city' => 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ',
    'goal' => '2',
    'industry' => '4'
));

$che = 'ok';
foreach ($result['body'] as $num=>$row) {
    //Denis
    if ($row['user_id'] == 3 && $row['start_time'] != strtotime($day.'12:00:00') && $row['end_time'] != strtotime($day.'13:00:00')) $che = 3;
    //Sergey
    if ($row['user_id'] == 4 && $row['start_time'] != strtotime($day.'10:00:00') && $row['end_time'] != strtotime($day.'12:00:00')) $che = 4;
    //Leonid
    if ($row['user_id'] == 5 && $row['start_time'] != strtotime($day.'10:00:00') && $row['end_time'] != strtotime($day.'12:00:00')) $che = 5;
    //Slava
    if ($row['user_id'] == 6 && $row['start_time'] != strtotime($day.'10:00:00') && $row['end_time'] != strtotime($day.'18:00:00')) $che = 6;
    //Anton
    if ($row['user_id'] == 7) $che = 7;
    //Egor
    if ($row['user_id'] == 8) $che = 8;
    //Vitaly
    if ($row['user_id'] == 9) $che = 9;
    //Selvestor
    if ($row['user_id'] == 10 && $row['start_time'] != strtotime($day.'14:45:00') && $row['end_time'] != strtotime($day.'15:45:00')) $che = 10;
    //Janik
    if ($row['user_id'] == 11) $che = 11;
}

if (!is_numeric($che)) {
    echo 'ok';
}
else {
    echo $che;
}