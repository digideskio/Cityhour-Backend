<?php

include_once("../application/models/Texts.php");

$config = parse_ini_file("../application/configs/application.ini");
$db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
die("Could not connect: " . mysql_error());
mysql_select_db($config['resources.db.params.dbname']);
mysql_set_charset('utf8',$db);
mysql_query("SET NAMES utf8, time_zone = '+0:00'");

$result = mysql_query("
                        select id,user_id,place
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
    while ($row = mysql_fetch_assoc($result)) {
        $data['place'] = $row['place'];
        $text = Application_Model_Texts::notification($data)[11];
        $id = $row["id"];
        $user_id = $row["user_id"];
        mysql_query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','11','1','4','$id','$text')");
    }
}
mysql_free_result($result);


$result = mysql_query("
                        select id,user_id,place
                        from calendar c
                        where start_time between now() + interval 85 minute and now() + interval 90 minute
                        and type = 2
                        and status = 2
                       ");
if (!$result) {
    echo "Could not successfully run query from DB: " . mysql_error();
    exit;
}


if (mysql_num_rows($result) != 0) {
    $text2 = Application_Model_Texts::push()[8];
    while ($row = mysql_fetch_assoc($result)) {
        $data['place'] = $row['place'];
        $text = Application_Model_Texts::notification($data)[10];
        $id = $row["id"];
        $user_id = $row["user_id"];
        $data = array(
            'from' => 0,
            'type' => 8,
            'item' => $id,
            'action' => 7
        );
        $data = json_encode($data);
        mysql_query("insert into push_messages (`user_id`, `type`, `alert`, `data`) values ('$user_id','8','$text2','$data')");
        mysql_query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','10','7','3','$id','$text')");
    }
}
mysql_free_result($result);

mysql_close($db);