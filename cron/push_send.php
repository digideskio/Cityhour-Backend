<?php
require "db.class.php";
$db = new DBcron();

while (true) {
    $all = $db->query("select group_concat(id) as ids from push_messages where `status` = 0",false,true);
    if (isset($all['ids'])) {
        echo exec('/bin/bash push_send.sh '.$all['ids']);
    }
    else {
        sleep(5);
    }
}