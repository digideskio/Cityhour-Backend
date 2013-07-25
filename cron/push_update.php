<?php

$config = parse_ini_file("../application/configs/application.ini");
$db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
die("Could not connect: " . mysql_error());
mysql_select_db($config['resources.db.params.dbname']);

$result = mysql_query("
                        select id
                        from push
                        where mtime < (now() - INTERVAL 1 Month)
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
    $id = $row["id"];
    mysql_query("DELETE FROM push WHERE id = $id");
}

mysql_free_result($result);

mysql_close($db);