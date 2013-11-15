<?php

require ("../application/models/Texts.php");

require "db.class.php";
$db = new DBcron();


$result = $db->query("
                        select id,user_id,place,user_id_second
                        from calendar c
                        where end_time between now() - interval 1445 minute and now() - interval 24 hour
                        and type = 2
                        and status = 2
                        and rating is null
                       ",false,true);

foreach ($result as $row) {
    $data['place'] = $row['place'];
    $text = Application_Model_Texts::notification($data)[11];
    $text = mysql_real_escape_string($text);
    $id = $row["id"];
    $user_id = $row["user_id"];
    $user_id_second = $row["user_id_second"];
    if ($id && $user_id && $user_id_second) {
        $db->query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('$user_id_second','$user_id','11','1','0','$id','$text')");
    }
}




$result = $db->query("
                        select id,user_id,place
                        from calendar c
                        where start_time between now() + interval 85 minute and now() + interval 90 minute
                        and type = 2
                        and status = 2
                       ",false,true);

$text2 = Application_Model_Texts::push()[8];
foreach ($result as $row) {
    $data['place'] = $row['place'];
    $text = Application_Model_Texts::notification($data)[10];
    $text = mysql_real_escape_string($text);
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
    mysql_query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','10','7','0','$id','$text')");
}