<?php

require "db.class.php";
$db = new DBcron();

$result = $db->query("
                        select id
                        from push
                        where mtime < (now() - INTERVAL 1 Month)
                       ",false,true);


foreach ($result as $row) {
    $id = $row["id"];
    if ($id) {
        $db->query("DELETE FROM push WHERE id = $id");
    }
}