<?php


function get_nearest_timezone($cur_lat, $cur_long, $country_code = '') {
    $timezone_ids = ($country_code) ? DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code)
        : DateTimeZone::listIdentifiers();

    if($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {

        $time_zone = '';
        $tz_distance = 0;

        //only one identifier?
        if (count($timezone_ids) == 1) {
            $time_zone = $timezone_ids[0];
        } else {

            foreach($timezone_ids as $timezone_id) {
                $timezone = new DateTimeZone($timezone_id);
                $location = $timezone->getLocation();
                $tz_lat   = $location['latitude'];
                $tz_long  = $location['longitude'];

                $theta    = $cur_long - $tz_long;
                $distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat)))
                    + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
                $distance = acos($distance);
                $distance = abs(rad2deg($distance));

                if (!$time_zone || $tz_distance > $distance) {
                    $time_zone   = $timezone_id;
                    $tz_distance = $distance;
                }

            }
        }
        return  $time_zone;
    }
    return false;
}

function get_nearest_timezone_google($lat, $lng) {
    $url = "https://maps.googleapis.com/maps/api/timezone/json?location=$lat,$lng&timestamp=1331161200&sensor=false";

    $ctx = stream_context_create(array('http'=>
                                           array(
                                               'timeout' => 8, // Seconds x2
                                           )
        ));

    if (!$data = @file_get_contents($url, false, $ctx)) {
        return get_nearest_timezone($lat, $lng);
    }

    if ($data = json_decode($data,true)) {
        if (isset($data['timeZoneId']) && $data['timeZoneId'] && isset($data['status']) && $data['status'] == 'OK') {
            return $data['timeZoneId'];
        }
    }

    return get_nearest_timezone($lat, $lng);
}

function getOffset($lat,$lng) {
    if ($zone = get_nearest_timezone_google($lat,$lng)) {
        $z = new DateTimeZone($zone);
        return $z->getOffset((new DateTime()));
    }

    return false;
}

include_once('db.class.php');
$db = new DBcron();

if ($users = $db->query("
        select s.id, s.value as offset, c.city_name as city, c.lat, c.lng
        from user_settings s
        left join user_settings s2 on s.user_id = s2.user_id and s2.name = 'city'
        left join city c on s2.value = c.city
        where s.name = 'offset'
    ",false,true)) {
    foreach ($users as $row) {
        $off = getOffset($row['lat'],$row['lng']);
        $id = $row['id'];
        var_dump($row['city'],$off);
        $db->query("update user_settings set value = $off where id = $id");
    }
}

echo getOffset(50.4501, 30.5234);