<?php

$config = parse_ini_file("../application/configs/application.ini");
$db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
die("Could not connect: " . mysql_error());
mysql_select_db($config['resources.db.params.dbname']);

$result = mysql_query("
                        select id,user_id
                        from calendar c
                        where end_time between now() - interval 1445 minute and now() - interval 24 hour
                        and type = 2
                        and status = 2
                        and rating is null
                       ");

if (!$result) {
    echo "Could not successfully run query from DB: " . mysql_error();
    exit;
}

if (mysql_num_rows($result) != 0) {
    $text = 'Rate meeting!';
    while ($row = mysql_fetch_assoc($result)) {
        $id = $row["id"];
        $user_id = $row["user_id"];
        mysql_query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','11','1','0','$id','$text')");
    }
}
mysql_free_result($result);


$result = mysql_query("
                        select id,user_id
                        from calendar c
                        where start_time between now() + interval 55 minute and now() + interval 1 hour
                        and type = 2
                        and status = 2
                       ");
if (!$result) {
    echo "Could not successfully run query from DB: " . mysql_error();
    exit;
}


if (mysql_num_rows($result) != 0) {
    $text = 'Meeting COME!';
    while ($row = mysql_fetch_assoc($result)) {
        $id = $row["id"];
        $user_id = $row["user_id"];
        $data = array(
            'from' => 0,
            'type' => 8,
            'item' => $id,
            'action' => 1
        );
        mysql_query("insert into push_messages (`user_id`, `type`, `alert`, `data`) values ('$user_id','8','$text','$data')");
        mysql_query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','10','1','0','$id','$text')");
    }
}
mysql_free_result($result);

mysql_close($db);