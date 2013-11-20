<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 3970;

while ($i < 4051) {
    // Delete Users
    $db->deleteUser($i);

    $i = $i+1;
}