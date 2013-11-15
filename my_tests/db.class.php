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


    public function addLanguages($i,$langs) {
        foreach ($langs as $row) {
            $this->query("
                INSERT INTO `user_languages` (`user_id`, `languages_id`)
                VALUES
                    ($i, '$row')
            ");
        }
        return true;
    }

    public function answer($result) {
        $this->mysql = null;
        var_dump($result);
        die();
    }

    public function clearTable($table) {
        $this->query("truncate table $table");
        return true;
    }

    public function insertSlot($user_id, $user_id_second, $start_time, $end_time, $goal, $type, $status) {
        $this->query("
            INSERT INTO `calendar` (`user_id`, `user_id_second`, `start_time`, `end_time`, `goal`, `city`, `city_name`, `lat`, `lng`, `type`, `status`)
            VALUES
              ('$user_id', '$user_id_second', '$start_time', '$end_time', '$goal', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ', 'Kiev, Kyiv city, Ukraine', '50.4501', '30.5234', '$type', '$status')
        ");
        return true;
    }


    public function insertUser($i,$name,$industry,$goal,$is_free,$offset = 0,$city = 0) {

        $this->deleteUser($i);

        // Kiev
        if ($city === 0) {
            $city_id = 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ';
            $city_name = 'Kiev, UA';
            $foursquare_id = '4cb580693ac937047b93cc0a';
            $foursquare_name = 'Мафия / Mafia';
            $lat = rand(502521, 505521)/10000;
            $lng = rand(301263, 306263)/10000;
            $offset = 7200;
        }
        // New York
        elseif ($city === 1) {
            $city_id = 'CjQuAAAA_AceaICSw-gXqX1F3tn6w0E6UR5Vz4TYG8jufIELDRrxN5zakqlcEkGlliQnZUjrEhA5NQTNQHWPfYzbDcU8pLYBGhQQja2U1Cn_gP3NrPVfgV2R6qyy5A';
            $city_name = 'New York, US';
            $foursquare_id = '44c91345f964a520f3351fe3';
            $foursquare_name = 'Max Brenner';
            $lat = rand(404960, 409153)/10000;
            $lng = rand(737003,742557)/10000;
            $lng = (float)-$lng;
            $offset = -18000;
        }
        // London
        elseif ($city === 2) {
            $city_id = 'CjQuAAAAmHGV3zPaj2a-U2_MppLxfIUkKCKR0UJQw8vcRcB9-e7sa2PngbT4dVUxy4eBZ4YxEhBiSdBrnd8-38NqBLedM4YMGhQNsl-6lVDLnrx0r56lpSePtB0x5Q';
            $city_name = 'London, GB';
            $foursquare_id = '51065455e4b06f162ac7a347';
            $foursquare_name = 'Balthazar';
            $lat = rand(513849, 516723)/10000;
            $lng = rand(-3514,1482)/10000;
            $offset = 0;
        }
        // LA
        elseif ($city === 3) {
            $city_id = 'CkQ2AAAAuA0PZXI4tyFumXKB9rZ875QWqXNIcCCwClBtqiTdqp8Kfk81trtf4DpcQWnhxd7xSNw2pv_2T3nUjB7Wj6VZbBIQ4smK_Lq4xD3VIAgggtGMKhoUjaHqMFtkzEH6jMP_Hx9720I9q7Q';
            $city_name = 'Los Angeles, US';
            $foursquare_id = '4a596c45f964a5205fb91fe3';
            $foursquare_name = 'Club Nokia';
            $lat = rand(337037, 343373)/10000;
            $lng = rand(1181550,1186680)/10000;
            $lng = (float)-$lng;
            $offset = -28800;
        }
        // Moscow
        elseif ($city === 4) {
            $city_id = 'CjQmAAAA0PyA3BoSklzQzZY8tn_bnxY7N6YbD1UN4dysIO-7bDIVdw4wfhvCLxNc56ZL-1v7EhAl1I_hpFHXPz1RH5bcYOcgGhSFsv59Kc_JFHXvkpttjq4_lS3EtQ';
            $city_name = 'Moscow, RU';
            $foursquare_id = '4b7757e8f964a5209b932ee3';
            $foursquare_name = 'Hard Rock Cafe Москва';
            $lat = rand(554899, 560097)/10000;
            $lng = rand(373193,379457)/10000;
            $offset = 14400;
        }


        $this->query("
            INSERT INTO `users` (`id`, `email`, `name`, `lastname`, `industry_id`, `summary`, `photo`, `phone`, `business_email`, `city`, `city_name`, `free_city`, `free_city_name`, `free_lat`, `free_lng`, `skype`, `rating`, `experience`, `completeness`, `contacts`, `meet_succesfull`, `meet_declined`, `facebook_key`, `facebook_id`, `linkedin_key`, `linkedin_id`, `private_key`, `status`, `free_foursquare_id`,`free_place`)
            VALUES
                ($i,'Dummy$i@gmail.com', '$name', 'Badum$i', $industry , 'Summar', '1.png', '+1234567890', 'Dummy$i@gmail.com', '$city_id', '$city_name', '$city_id', '$city_name', $lat, $lng, 'dumbldore', 0, 3, 100, 5, 0, 0, null, null, null, null, '$i', 0,'$foursquare_id','$foursquare_name')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'city', '$city_id');
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'free_time', '$is_free')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'foursquare_id', '$foursquare_id')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'offset', '$offset')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'goal', '$goal')
        ");

        $this->query("
            INSERT INTO `user_jobs` (`user_id`, `name`, `company`, `current`, `start_time`, `end_time`, `type`)
            VALUES
                ($i, 'Back-end Developer', 'Alterplay', 1, '2012-11-01', '2012-11-01', 0)

        ");
        return true;
    }

    public function insertCoolUser($i,$name,$surname,$email,$industry,$goal,$foursquare,$place,$summar,$photo,$phone,$lat,$lng,$skype,$rating,$experience,$city,$city_name) {

        $this->deleteUser($i);
        $contacts = rand(1, 100);
        $completnes = rand(75, 100);
        $meet_succesfull = rand(2, 10);
        $this->query("
            INSERT INTO `users` (`id`, `email`, `name`, `lastname`, `industry_id`, `summary`, `photo`, `phone`, `business_email`, `city`, `city_name`, `free_city`, `free_city_name`, `free_lat`, `free_lng`, `skype`, `rating`, `experience`, `completeness`, `contacts`, `meet_succesfull`, `meet_declined`, `facebook_key`, `facebook_id`, `linkedin_key`, `linkedin_id`, `private_key`, `status`, `free_foursquare_id`,`free_place`, `country`)
            VALUES
                ($i,'$email', '$name', '$surname', $industry , '$summar', '$photo', '$phone', '$email', '$city', '$city_name', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ', 'Kiev, UA', $lat, $lng, '$skype', $rating, $experience, $completnes, $contacts, $meet_succesfull, 0, null, null, null, null, '$i', 0,'$foursquare','$place','Ukraine')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'city', 'CjQwAAAAJHPmzNEn1Cua_WzzFbYs-GYddXWorsn7RDUkiv5q43UggZSn4m8opMwDXHqvr-lCEhCuJnsTC4WpqcTN_4U1TNmQGhRgnGNUy37EyI6l_HbuGuQ_wt7tbQ');
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'free_time', '1')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'foursquare_id', '$foursquare')
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'offset', 7200)
        ");

        $this->query("
            INSERT INTO `user_settings` (`user_id`, `name`, `value`)
            VALUES
                ($i, 'goal', '$goal')
        ");
        return true;
    }

    public function addJob($user_id, $name, $company, $current, $start_time, $end_time, $type) {
        $this->query("
            INSERT INTO `user_jobs` (`user_id`, `name`, `company`, `current`, `start_time`, `end_time`, `type`)
            VALUES
                ($user_id, '$name', '$company', $current, '$start_time', '$end_time', $type)
        ");
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

        $this->query("
            DELETE FROM user_languages
            WHERE user_id = $i
        ");
        return true;
    }

}