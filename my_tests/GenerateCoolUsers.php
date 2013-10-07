<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 1;

// Insert Users
$db->insertCoolUser(1000,'Pmpum'.$i,$industry,2,1,10800);



mysql_close($db);
