<?php

$config = parse_ini_file("../application/configs/application.ini");
$db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
die("Could not connect: " . mysql_error());
mysql_select_db($config['resources.db.params.dbname']);


$i = 1;

while ($i < 40) {
    mysql_query("
        DELETE FROM users
        WHERE id = $i
    ");

    mysql_query("
        DELETE FROM user_settings
        WHERE user_id = $i
    ");

    mysql_query("
        DELETE FROM user_jobs
        WHERE user_id = $i
    ");

    $industry = rand(4,5);
    mysql_query("
        INSERT INTO `users` (`id`, `email`, `name`, `lastname`, `industry_id`, `summary`, `photo`, `phone`, `business_email`, `city`, `city_name`, `lat`, `lng`, `skype`, `rating`, `experience`, `completeness`, `contacts`, `meet_succesfull`, `meet_declined`, `facebook_key`, `facebook_id`, `linkedin_key`, `linkedin_id`, `private_key`, `status`)
        VALUES
            ($i,'Dummy$i@gmail.com', 'Dum$i', 'Badum$i', $industry , 'Summar', 'circle_137725438252173bee9461f.png', '+1234567890', 'Dummy$i@gmail.com', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ', 'Kiev, Kyiv city, Ukraine', 50.4501, 30.5234, 'dumbldore', 0, 3, 100, 5, 0, 0, null, null, null, null, null, 0)
    ");

    mysql_query("
        INSERT INTO `user_settings` (`user_id`, `name`, `value`)
        VALUES
            ($i, 'city', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ');
    ");

    mysql_query("
        INSERT INTO `user_settings` (`user_id`, `name`, `value`)
        VALUES
            ($i, 'free_time', '1')
    ");

    mysql_query("
        INSERT INTO `user_jobs` (`user_id`, `name`, `company`, `current`, `start_time`, `end_time`, `type`)
        VALUES
            ($i, 'IOS Developer', 'Alterplay', 1, '2012-11-01', '2012-11-01', 0)

    ");
    $i = $i+1;
}



mysql_close($db);