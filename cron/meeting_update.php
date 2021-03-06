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
    $text = $db->quote($text);
    $id = $row["id"];
    $user_id = $row["user_id"];
    $user_id_second = $row["user_id_second"];
    if ($id && $user_id && $user_id_second) {
        $db->query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('$user_id_second','$user_id','11','1','0','$id',$text)");
    }
}




$result = $db->query("
                        select c.id,c.user_id,c.place,u.name,u.lastname,c.user_id_second
                        from calendar c
                        left join users u on c.user_id_second = u.id
                        where c.start_time between now() + interval 85 minute and now() + interval 90 minute
                        and c.type = 2
                        and c.status = 2
                       ",false,true);

foreach ($result as $row) {
    $data['place'] = $row['place'];
    $text = Application_Model_Texts::notification($data)[10];

    $to = $row['user_id'];
    $from = $row['user_id_second'];
    $che = $db->query("
            select id
            from user_friends
            where user_id=$from and friend_id=$to
            and status = 1
            limit 1
        ",true,false);

    if (!isset($che['id'])) {
        $lastname = trim($row['lastname'])[0].'.';
    }
    else {
        $lastname = $row['lastname'];
    }

    $fullName['name'] = $row['name'].' '.$lastname;
    $text2 = Application_Model_Texts::push($fullName)[8];

    $text = $db->quote($text);
    $id = $row["id"];
    $user_id = $row["user_id"];
    $data = array(
        'from' => 0,
        'type' => 8,
        'item' => $id,
        'action' => 7
    );
    $data = json_encode($data);
    $db->query("insert into push_messages (`user_id`, `type`, `alert`, `data`) values ('$user_id','8','$text2','$data')");
    $db->query("insert into notifications (`from`, `to`, `type`, `action`, `template`, `item`, `text`) values ('0','$user_id','10','7','0','$id',$text)");
}