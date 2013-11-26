<?php

require "db.class.php";
$db = new DBcron();

//  file here
require '../vendor/mixpanel/mixpanel-php/lib/Mixpanel.php';
$mp = Mixpanel::getInstance("a77be1f360ca8017da9603631231f524");

$result = $db->query("
                        select c.id,c.start_time,c.end_time,c.place,c.city_name,u.industry_id,c.lat,c.lng,c.goal,c.offset,c.email,c.user_id,c.user_id_second, i.name as industry
                        from calendar c
                        left join users u on c.user_id = u.id
                        left join industries i on u.`industry_id` = i.id
                        where c.start_time between now() - interval 15 minute and now()
                        and c.type = 2
                        and c.status = 2
                       ",false,true);

foreach ($result as $row) {
    if ($id = $row["user_id"]) {
        $mp->identify($id);
        $mp->track('Meeting started',array(
            'id' => $row['id'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'place' => $row['place'],
            'city_name' => $row['city_name'],
            'industry' => $row['industry'],
            'industry_id' => $row['industry_id'],
            'lat' => $row['lat'],
            'lng' => $row['lng'],
            'goal' => $row['goal'],
            'offset' => $row['offset'],
            'email' => $row['email'],
            'user_id' => $row['user_id'],
            'user_id_second' => $row['user_id_second']
        ));
    }
}