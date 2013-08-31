<?php

$config = parse_ini_file("../application/configs/application.ini");
$db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
die("Could not connect: " . mysql_error());
mysql_select_db($config['resources.db.params.dbname']);

$result = mysql_query("
                        select group_concat(c.user_id) as users
                        from calendar c
                        where
                        c.type = 2
                        and c.status = 2
                        and c.end_time between (now() - interval 1 hour) and now()
                       ");

if (!$result) {
    echo "Could not successfully run query from DB: " . mysql_error();
    exit;
}

if (mysql_num_rows($result) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}

while ($row = mysql_fetch_assoc($result)) {
    $ids = $row["users"];
    mysql_query("update users set `meet_succesfull` = `meet_succesfull`+1 where id in ($ids)");
}

mysql_free_result($result);

mysql_close($db);