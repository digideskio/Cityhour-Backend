<?php

require "db.class.php";
$db = new DBcron();

//  file here
require '../vendor/mixpanel/mixpanel-php/lib/Mixpanel.php';
$mp = Mixpanel::getInstance("a77be1f360ca8017da9603631231f524");

$result = $db->query("
                        select u.id, u.name, u.meet_declined, u.meet_succesfull, (
                            select count(c.id)
                            from calendar c
                            where c.user_id = u.id
                            and c.start_time > now()
                            and c.status = 2
                            and c.type = 2
                        ) as meet_will
                        from users u
                   ",false,true);

foreach ($result as $row) {
    if ($id = $row["id"]) {
        $mp->people->set($id, array(
            'id' => $row['id'],
            'name' => $row['name'],
            'meet_declined'       => $row['meet_declined'],
            'meet_succesfull'        => $row['meet_succesfull'],
            'meet_will'            => $row['meet_will']
        ));

    }
}