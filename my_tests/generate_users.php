<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 2050;

while ($i < 2450) {
    $industry = rand(4,5);
    $goal = rand(1,3);

    // Insert Users
    $db->insertUser($i,'Auto User'.$i,$industry,$goal,1,7200);

    $i = $i+1;
}
