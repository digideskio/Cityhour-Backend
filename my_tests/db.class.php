<?php

class DB {

    /** @var resource $mysql DB connector */
    var $mysql;

    /** @var array $config Config */
    var $config;

    public function connect() {
        /**
         * Connect to DB
         */
        $this->config = parse_ini_file("../application/configs/application.ini");
        $host = $this->config['resources.db.params.host'];
        $username = $this->config['resources.db.params.username'];
        $password = $this->config['resources.db.params.password'];
        $dbname = $this->config['resources.db.params.dbname'];

        try {
            $this->mysql = new PDO("mysql:host=$host;dbname=$dbname", "$username", "$password");
            $this->mysql->exec("SET NAMES utf8, time_zone = '+0:00'");
            date_default_timezone_set("UTC");
            $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(Exception $e)
        {
            $this->answer($e,500);
            die();
        }
    }

    public function query($sql,$fetch = false,$fetchAll = false){
        try {
            $result = $this->mysql->query($sql);
            if ($fetch) {
                $result = $result->fetch(PDO::FETCH_ASSOC);
            }
            if ($fetchAll) {
                $result = $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        catch(Exception $e)
        {
            $this->answer($e,500);
            die();
        }
        return $result;
    }


    public function answer($result) {
        $this->mysql = null;
        var_dump($result);
        die();
    }


    public function insertSlot($user_id, $user_id_second, $start_time, $end_time, $goal, $type, $status) {
        $this->query("
            INSERT INTO `calendar` (`user_id`, `user_id_second`, `start_time`, `end_time`, `goal`, `city`, `city_name`, `lat`, `lng`, `type`, `status`)
            VALUES
              ('$user_id', '$user_id_second', '$start_time', '$end_time', '$goal', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ', 'Kiev, Kyiv city, Ukraine', '50.4501', '30.5234', '$type', '$status')
        ");
        return true;
    }


    public function insertUser($i,$name,$industry,$goal,$is_free) {

        $this->deleteUser($i);

        $this->query("
            INSERT INTO `users` (`id`, `email`, `name`, `lastname`, `industry_id`, `summary`, `photo`, `phone`, `business_email`, `city`, `city_name`, `lat`, `lng`, `skype`, `rating`, `experience`, `completeness`, `contacts`, `meet_succesfull`, `meet_declined`, `facebook_key`, `facebook_id`, `linkedin_key`, `linkedin_id`, `private_key`, `status`)
            VALUES
                ($i,'Dummy$i@gmail.com', '$name', 'Badum$i', $industry , 'Summar', '1.jpg', '+1234567890', 'Dummy$i@gmail.com', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ', 'Kiev, Kyiv city, Ukraine', 50.4501, 30.5234, 'dumbldore', 0, 3, 100, 5, 0, 0, null, null, null, null, '$i', 0)
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'city', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ');
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'free_time', '$is_free')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'goal', '$goal')
        ");

        $this->query("
            INSERT INTO `user_jobs` (`user_id`, `name`, `company`, `current`, `start_time`, `end_time`, `type`)
            VALUES
                ($i, 'IOS Developer', 'Alterplay', 1, '2012-11-01', '2012-11-01', 0)

        ");
        return true;
    }

    public function deleteUser($i) {
        $this->query("
            DELETE FROM calendar
            WHERE user_id = $i
        ");

        $this->query("
            DELETE FROM calendar
            WHERE user_id_second = $i
        ");

        $this->query("
            DELETE FROM users
            WHERE id = $i
        ");

        $this->query("
            DELETE FROM user_settings
            WHERE user_id = $i
        ");

        $this->query("
            DELETE FROM user_jobs
            WHERE user_id = $i
        ");
        return true;
    }

}