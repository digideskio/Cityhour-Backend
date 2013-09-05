<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 1;

while ($i < 40) {
    $industry = rand(4,5);

    // Insert Users
    $db->deleteUser($i);

    $i = $i+1;
}



mysql_close($db);