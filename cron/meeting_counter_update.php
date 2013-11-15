<?php

require "db.class.php";
$db = new DBcron();

$result = $db->query("
                        select group_concat(c.user_id) as users
                        from calendar c
                        where
                        c.type = 2
                        and c.status = 2
                        and c.end_time between (now() - interval 1 hour) and now()
                       ",false,true);

foreach ($result as $row) {
    $ids = $row["users"];
    if ($ids) {
        $db->query("update users set `meet_succesfull` = `meet_succesfull`+1
                where id in ($ids)");
    }
}