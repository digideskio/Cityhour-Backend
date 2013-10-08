<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 1;

while ($i < 10) {
    $industry = rand(4,5);

    // Insert Users
    $db->insertUser($i,'Pmpum'.$i,$industry,2,1,10800);

    $i = $i+1;
}
