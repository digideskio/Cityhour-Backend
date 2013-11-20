<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$i = 3000;

while ($i < 3970) {
    // Delete Users
    $db->deleteUser($i);

    $i = $i+1;
}