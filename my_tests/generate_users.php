<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 3000;

while ($i < 4000) {
    $industry = rand(4,5);
    $goal = rand(1,3);
    $city = rand(1,3);

    // Insert Users
    $db->insertUser($i,'Auto User'.$i,$industry,$goal,1,0,$city);

    $i = $i+1;
}
