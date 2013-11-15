<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 4000;

while ($i < 4050) {
    $industry = rand(4,5);
    $goal = rand(1,3);
    $city = 4;

    // Insert Users
    $db->insertUser($i,'Auto User'.$i,$industry,$goal,1,0,$city);

    $i = $i+1;
}
