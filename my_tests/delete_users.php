<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 2000;

while ($i < 2500) {
    // Delete Users
    $db->deleteUser($i);

    $i = $i+1;
}