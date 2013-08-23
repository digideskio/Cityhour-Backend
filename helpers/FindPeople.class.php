<?php

class FindPeople {
    var $debug;

    /** @var resource $mysqli DB connector */
    var $mysql;

    /** @var boolean $map Map request or no */
    var $map;

    /** @var array $config Config */
    var $config;

    /** @var string $temp_t name of temporary table for main select */
    /** @var string $b_s Bussines time start */
    /** @var string $b_e Bussines time end */
    /** @var string $q_s Query time start */
    /** @var string $q_e Query time end */
    /** @var int $goal Query goal */
    /** @var int $industry Query industry */
    /** @var float $n_lat Query city square */
    /** @var float $s_lat Query city square */
    /** @var float $n_lng Query city square */
    /** @var float $s_lng Query city square */
    var $temp_t;
    var $b_s;
    var $b_e;
    var $q_s;
    var $q_e;
    var $goal;
    var $industry;
    var $n_lat;
    var $s_lat;
    var $n_lng;
    var $s_lng;
    var $industry_q;
    var $goal_r;
    var $goal_nf;
    var $goal_fn;
    var $goal_f;

    /** @var int $user_id Id of user */
    var $user_id;


    public function __construct($debug,$map) {
        $this->debug = $debug;
        $this->map = $map;
        $this->mainWork();
    }

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
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            die();
        }
    }

    public function getUser($token) {
        $sql = "
            select id
            from users
            where private_key = '$token'
        ";

        try {
            $result = $this->mysql->query($sql);
            $result = $result->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            die();
        }
        if (isset($result['id'])) {
            $this->user_id = $result['id'];
            return $result;
        }
        else {
            return false;
        }
    }

    public function getCity($city) {
        $sql = "
            select city, n_lat, n_lng, s_lat, s_lng
            from city
            where city = '$city'
        ";
        try {
            $result = $this->mysql->query($sql);
            $result = $result->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            die();
        }


        if (isset($result['city'])) {
            return $result;
        }
        else {
            $url = $this->config['google.url'].(string)$city;
            $req = json_decode(file_get_contents($url), true);

            if ($req['status'] == 'OK') {
                $name = $req['result']['formatted_address'];
                $data['city'] = $city;
                $lat = $req['result']['geometry']['location']['lat'];
                $lng = $req['result']['geometry']['location']['lng'];

                if (isset($req['result']['geometry']['viewport'])) {
                    $data['n_lng'] = $req['result']['geometry']['viewport']['northeast']['lng'];
                    $data['s_lng'] = $req['result']['geometry']['viewport']['southwest']['lng'];
                    $data['n_lat'] = $req['result']['geometry']['viewport']['northeast']['lat'];
                    $data['s_lat'] = $req['result']['geometry']['viewport']['southwest']['lat'];
                }
                else {
                    $data['n_lng'] = (float)$data['lng']+0.1;
                    $data['s_lng'] = (float)$data['lng']-0.1;
                    $data['n_lat'] = (float)$data['lat']+0.1;
                    $data['s_lat'] = (float)$data['lat']-0.1;
                }

                $s_lng = $data['s_lng'];
                $n_lng = $data['n_lng'];
                $n_lat = $data['n_lat'];
                $s_lat = $data['s_lat'];

                $sql = "
                    INSERT INTO `city` (`city`, `city_name`, `lat`, `lng`, `n_lat`, `n_lng`, `s_lat`, `s_lng`)
                    VALUES
	                ('$city', '$name', $lat, $lng, $n_lat, $n_lng, $s_lat, $s_lng)
                ";
                try {
                    $this->mysql->query($sql);
                }
                catch(PDOException $e)
                {
                    $this->answer($e->getMessage(),500);
                    die();
                }

                return $data;
            }
            else {
                return false;
            }
        }
    }

    public function mapUpdate($user_id,$lat,$lng) {
        $sql = "
            select id
            from `map`
            where user_id = $user_id
        ";

        try {
            $result = $this->mysql->query($sql);
            $result = $result->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            die();
        }

        $time = date('Y-m-d H:i:s',time());
        if (isset($result['id'])) {
            $id = $result['id'];

            $sql = "
                    update `map` set `lat` = $lat, `lng` = $lng, `time` = '$time'
                    where id = $id and user_id = $user_id
                ";
            try {
                $this->mysql->query($sql);
            }
            catch(PDOException $e)
            {
                $this->answer($e->getMessage(),500);
                die();
            }
        }
        else {
            $sql = "
                    INSERT INTO `map` (`user_id`, `lat`, `lng`, `time`)
                    VALUES
	                ($user_id,$lat,$lng,'$time')
                ";
            try {
                $this->mysql->query($sql);
            }
            catch(PDOException $e)
            {
                $this->answer($e->getMessage(),500);
                die();
            }
        }
    }

    public function getValues() {
        $this->temp_t = '`'.uniqid().'`';

        if (!$this->map) {
            $che = true;

            if (isset($_POST["private_key"])) $token = $_POST["private_key"]; else $che = false;

            if (isset($_POST["data_from"])) $this->q_s = $_POST["data_from"]; else $che = false;
            if (isset($_POST["data_to"])) $this->q_e = $_POST["data_to"]; else $che = false;

            if (isset($_POST["city"])) $city = $_POST["city"]; else $che = false;

            if (isset($_POST["goal"])) $this->goal = $_POST["goal"];
            if (isset($_POST["industry"])) $this->industry = $_POST["industry"];

            if (!$che) {
                $this->answer('Not all params given',400);
                die();
            }
            if ( $token != null && $token != '' && is_numeric($this->q_s) && is_numeric($this->q_e) &&  $city != null && $city != '') {
                $user = $this->getUser($token);
                if ($user) {
                    $city = $this->getCity($city);
                    if ($city) {
                        $this->b_s = date("Y-m-d", $this->q_s).' 09:00:00';
                        $this->b_e = date("Y-m-d", $this->q_e).' 18:00:00';

                        $this->q_s = date("Y-m-d H:i:s", $this->q_s);
                        $this->q_e = date("Y-m-d H:i:s", $this->q_e);

                        $this->n_lat = $city['n_lat'];
                        $this->s_lat = $city['s_lat'];
                        $this->n_lng = $city['n_lng'];
                        $this->s_lng = $city['s_lng'];
                    }
                    else {
                        $this->answer('Bad city',400);
                        die();
                    }
                }
                else {
                    $this->answer('Authentication failed',401);
                    die();
                }
            }
            else {
                $this->answer('Not all params given',400);
                die();
            }
        }
        else {
            $che = true;

            if (isset($_POST["private_key"])) $token = $_POST["private_key"]; else $che = false;

            if (isset($_POST["n_lat"])) $this->n_lat = $_POST["n_lat"]; else $che = false;
            if (isset($_POST["s_lat"])) $this->s_lat = $_POST["s_lat"]; else $che = false;
            if (isset($_POST["n_lng"])) $this->n_lng = $_POST["n_lng"]; else $che = false;
            if (isset($_POST["s_lng"])) $this->s_lng = $_POST["s_lng"]; else $che = false;

            if (isset($_POST["lat"])) $lat = $_POST["lat"]; else $che = false;
            if (isset($_POST["lng"])) $lng = $_POST["lng"]; else $che = false;

            if (!$che) {
                $this->answer('Not all params given',400);
                die();
            }


            if ( $token != null && $token != '' && is_numeric($this->n_lat) && is_numeric($this->s_lat) && is_numeric($this->n_lng) && is_numeric($this->s_lng) && is_numeric($lat) && is_numeric($lng)) {
                $user = $this->getUser($token);
                if ($user) {
                    $this->mapUpdate($user['id'],$lat,$lng);

                    $time = time();

                    $this->q_s = date("Y-m-d H:i:s", $time );
                    $this->q_e = date("Y-m-d H:i:s", $time+7200 );

                    $this->b_s = date("Y-m-d", $time).' 09:00:00';
                    $this->b_e = date("Y-m-d", $time).' 18:00:00';
                }
                else {
                    $this->answer('Authentication failed',401);
                    die();
                }
            }
            else {
                $this->answer('Not all params given',400);
                die();
            }
        }


        if (isset($this->industry) && is_numeric($this->industry)) {
            $this->industry_q = " and u.industry_id = $this->industry ";
        }
        else {
            $this->industry_q = " ";
        }

        if (isset($this->goal) && is_numeric($this->goal)) {
            $this->goal_r = " select in_id from goals where id = $this->goal into @goals; ";
            $this->goal_nf = " and c.goal in (@goals) ";
            $this->goal_f = " and (select cast(s3.value as unsigned) from user_settings s3 where s3.user_id = c.user_id and s3.name = 'goal') in (@goals) ";
            $this->goal_fn = " and (select cast(s3.value as unsigned) from user_settings s3 where s3.user_id = u.id and s3.name = 'goal') in (@goals) ";
        }
        else {
            $this->goal_r = " ";
            $this->goal_nf = " ";
            $this->goal_f = " ";
            $this->goal_fn = " ";
        }

    }

    public function createT() {
        $sql = "
            create table $this->temp_t (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;

            create temporary table rSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;

            create temporary table zSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;

            create temporary table xSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;

            $this->goal_r

            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select c.id as id, c.user_id, c.type, s.value as is_free,
            CASE
                WHEN c.type=1 THEN GREATEST('$this->q_s', c.start_time)
                ELSE c.start_time
            end AS start_time,
            CASE
                WHEN c.type=1 THEN LEAST('$this->q_e', c.end_time)
                ELSE c.end_time
            end AS end_time

            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join users u on c.user_id = u.id

            where
            ( (c.start_time between '$this->q_s' and '$this->q_e') or (c.end_time between '$this->q_s' and '$this->q_e') or (c.start_time >= '$this->q_s' and c.end_time <= '$this->q_e') )

            and ( (c.type = 2 and c.status = 2)
            or ( c.type = 1
            and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_nf
            ))

            and (s.value is null or s.value = '0')
            having start_time != end_time;


            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select c.id, c.user_id, c.type, s.value as is_free,
            CASE
                WHEN c.type=1 THEN GREATEST('$this->q_s', c.start_time)
                ELSE c.start_time
            end AS start_time,
            CASE
                WHEN c.type=1 THEN LEAST('$this->q_e', c.end_time)
                ELSE c.end_time
            end AS end_time

            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join user_settings s2 on s2.user_id = c.user_id and s2.name = 'city'
            left join city ct on s2.value = ct.city
            left join users u on c.user_id = u.id

            where
            ( (c.start_time between '$this->q_s' and '$this->q_e') or (c.end_time between '$this->q_s' and '$this->q_e') or (c.start_time >= '$this->q_s' and c.end_time <= '$this->q_e') )

            and (
               ( c.type = 2 and c.status = 2)
            or ( c.type = 1
                and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
                and c.goal in (@goals)
               )
            or ( c.type = 0 and c.status = 0)
            )

            and s.value = '1'
            and ct.lng BETWEEN $this->s_lng AND $this->n_lng AND ct.lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_f

            having start_time != end_time;


            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select u.id as id, u.id as user_id, 3 as type, s.value as is_free, GREATEST('$this->q_s', '$this->b_s') as start_time, LEAST('$this->q_e', '$this->b_e') as end_time

            from users u
            left join user_settings s on s.user_id = u.id and s.name = 'free_time'
            left join user_settings s2 on s2.user_id = u.id and s2.name = 'city'
            left join city ct on s2.value = ct.city

            where
            s.value = '1'
            and ct.lng BETWEEN $this->s_lng AND $this->n_lng AND ct.lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_fn
            having start_time != end_time;

            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            (
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN $this->temp_t mt on t.user_id = mt.user_id
            and mt.type = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            and mt.id is null
            );
        ";

        try {
            $stmt = $this->mysql->prepare($sql);
            $stmt->execute();
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
    }

    public function MeetFreeCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN $this->temp_t mt on t.user_id = mt.user_id
            and mt.type = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            order by t.id,mt.start_time
        ";
        try {
            $result = $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function BusyRNotCross() {
        $sql = "
            insert into zSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN rSult ft on t.user_id = ft.user_id
            and ft.type = 1
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 0
            and t.is_free = 1
            and ft.id is null
        ";

        try {
            $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
    }

    public function BusyRCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN rSult mt on t.user_id = mt.user_id
            and mt.type = 1
            and mt.is_free = 1
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 0
            and t.is_free = 1
            order by t.id,mt.start_time
        ";

        try {
            $result = $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'zSult');
    }

    public function BusyPlusMeet() {
        $sql = "
            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN zSult ft on t.user_id = ft.user_id
            and ft.type = 0
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 2
            and t.is_free = 1
            and ft.id is null;

            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from zSult t
            left outer JOIN $this->temp_t ft on t.user_id = ft.user_id
            and ft.type = 2
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 0
            and t.is_free = 1
            and ft.id is null;

            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, 0 as 'type', t.is_free, LEAST(t.start_time, mt.start_time) AS start_time, greatest(t.end_time, mt.end_time) AS end_time
            from $this->temp_t t
            inner JOIN zSult mt on t.user_id = mt.user_id
            and mt.type = 0
            and mt.is_free = 1
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 2
            and t.is_free = 1;
        ";

        try {
            $stmt = $this->mysql->prepare($sql);
            $stmt->execute();
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
    }

    public function SetXNotCross() {
        $sql = "
            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN xSult ft on t.user_id = ft.user_id
            and t.start_time < ft.end_time
            and t.end_time > ft.start_time

            where t.type = 3
            and t.is_free = 1
            and ft.id is null
        ";
        try {
            $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
    }

    public function SetXCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN xSult mt on t.user_id = mt.user_id
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 3
            and t.is_free = 1
            order by t.id,mt.start_time
        ";

        try {
            $result = $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function getResult() {
        if (!$this->map) {
            $sql = "
                select t.user_id, t.start_time, t.end_time,
                    case
                      when t.type = 1 then (select c.foursquare_id from calendar c where c.id = t.id)
                      when t.type = 3 then (select s.value from user_settings s where s.name = 'foursquare_id' and s.user_id = t.user_id)
                      else null
                    end as foursquare_id,
                    case
                      when t.type = 1 then (select c.place from calendar c where c.id = t.id)
                      when t.type = 3 then (select ci.place from user_settings s left join place ci on ci.foursquare_id = s.value where s.name = 'foursquare_id' and s.user_id = t.user_id)
                      else null
                    end as place
                from (select * from rSult order by start_time asc) as t
                where (UNIX_TIMESTAMP(t.end_time) - UNIX_TIMESTAMP(t.start_time)) >= 3600
                and t.user_id != $this->user_id
                group by t.user_id
            ";
        }
        else {
            $url = $this->config['userPhoto.url'];
            $sql = "
                select t.user_id, t.lat, t.lng, u.name, u.lastname, concat('$url',u.photo) as photo, j.name as job_name, j.company
                from (
                    (
                        SELECT m.user_id, m.lat, m.lng
                        FROM map m
                        WHERE
                        m.time > now() - interval 10 MINUTE
                        and m.lng BETWEEN $this->s_lng AND $this->n_lng AND m.lat BETWEEN $this->s_lat AND $this->n_lat
                    )
                    union
                    (
                        select r.user_id,
                        case
                          when r.type = 1 then (select c.lat from calendar c where c.id = r.id)
                          when r.type = 3 then (select ci.lat from user_settings s left join city ci on ci.city = s.value where s.name = 'city' and s.user_id = r.user_id)
                          else null
                        end as lat,
                        case
                          when r.type = 1 then (select c.lng from calendar c where c.id = r.id)
                          when r.type = 3 then (select ci.lng from user_settings s left join city ci on ci.city = s.value where s.name = 'city' and s.user_id = r.user_id)
                          else null
                        end as lng
                        from rSult r
                        where (UNIX_TIMESTAMP(r.end_time) - UNIX_TIMESTAMP(r.start_time)) >= 3600
                    )
                ) as t
                left join users u on t.user_id = u.id
                left join user_jobs j on t.user_id = j.user_id
                WHERE t.user_id != $this->user_id
                and j.current=1
                group by t.user_id
            ";
        }

        try {
            $result = $this->mysql->query($sql);
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            $this->clearTempData();
            die();
        }

        if (!$result) {
            return array();
        }

        $res = $result->fetchAll(PDO::FETCH_ASSOC);

        return $res;
    }

    public function clearTempData() {

        $sql = "
            drop table $this->temp_t;
            drop table rSult;
            drop table zSult;
            drop table xSult;
        ";
        try {
            $stmt = $this->mysql->prepare($sql);
            $stmt->execute();
        }
        catch(PDOException $e)
        {
            $this->answer($e->getMessage(),500);
            die();
        }
    }

    public function mainWork() {
        $this->connect();
        $this->getValues();
        $this->createT();

        // Case A
        $this->MeetFreeCross();

        // Case B

        // Busy minus R
        $this->BusyRNotCross();
        $this->BusyRCross();

        // Busy plus meet
        $this->BusyPlusMeet();

        // Set - X
        $this->SetXNotCross();
        $this->SetXCross();

        // Get Result and Drop temporary data
        $result = $this->getResult();
        $this->clearTempData();
        $this->mysql = null;

        // Finish!!!
        $this->answer($result,200);
    }

    public function answer($result,$code) {
        if (!$this->debug) {
            if (!$this->map) {
                foreach ($result as $num => $row) {
                    $result[$num]['start_time'] = strtotime($row['start_time']);
                    $result[$num]['end_time'] = strtotime($row['end_time']);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(array(
                'body' => $result,
                'errorCode' => $code
            ));
        }
        else {
            var_dump($result);
        }
    }

    public function insertInto($result, $db) {
        if ($result) {
            $sql = "insert into $db (id, user_id, `type`, is_free, start_time, end_time) VALUES ";

            $first = true;
            foreach ($result as $row) {
                if (!$first) {
                    $sql .= ',';
                };
                $first = false;
                $sql .= "(".$row['id'].",".$row['user_id'].",".$row['type'].",".$row['is_free'].",'".$row['start_time']."','".$row['end_time']."')";
            }

            try {
                $this->mysql->query($sql);
            }
            catch(PDOException $e)
            {
                $this->answer($e->getMessage(),500);
                $this->clearTempData();
                die();
            }
        }
        return true;
    }

    public function getFreeArray($result) {
        if (!$result) {
            return array();
        }

        $last = array();
        $t_res = array();
        $res = array();
        $busy = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!$last) {
                array_push($busy,$row);
            }
            else {
                if ($last['id'] != $row['id']) {

                    $t_res[0] = array(
                        'id' => $last['id'],
                        'user_id' => $last['user_id'],
                        'type' => $last['type'],
                        'is_free' => $last['is_free'],
                        'start_time' => $last['start_time'],
                        'end_time' => $last['end_time'],
                    );

                    foreach ($busy as $busy_s) {
                        $l_res = $t_res;
                        $t_res = array();
                        foreach ($l_res as $tt_res) {
                            // left side
                            if ($tt_res['start_time'] <= $busy_s['second_start_time']) {
                                $free = array(
                                    'id' => $tt_res['id'],
                                    'user_id' => $tt_res['user_id'],
                                    'type' => $tt_res['type'],
                                    'is_free' => $tt_res['is_free'],
                                    'start_time' => $tt_res['start_time'],
                                    'end_time' => min($busy_s['second_start_time'], $tt_res['end_time']),
                                );
                                array_push($t_res,$free);
                            }

                            // right side
                            if ($tt_res['end_time'] >= $busy_s['second_end_time']) {
                                $free = array(
                                    'id' => $tt_res['id'],
                                    'user_id' => $tt_res['user_id'],
                                    'type' => $tt_res['type'],
                                    'is_free' => $tt_res['is_free'],
                                    'start_time' => max($busy_s['second_end_time'], $tt_res['start_time']),
                                    'end_time' => $tt_res['end_time'],
                                );
                                array_push($t_res,$free);
                            }
                        }
                    }

                    $busy = array();
                    array_push($busy,$row);

                    $res = array_merge($res,$t_res);
                    $t_res = array();
                }
                else {
                    array_push($busy,$row);
                }
            }
            $last = $row;
        }

        if (isset($last['id'])) {
            $t_res[0] = array(
                'id' => $last['id'],
                'user_id' => $last['user_id'],
                'type' => $last['type'],
                'is_free' => $last['is_free'],
                'start_time' => $last['start_time'],
                'end_time' => $last['end_time'],
            );

            foreach ($busy as $busy_s) {
                $l_res = $t_res;
                $t_res = array();
                foreach ($l_res as $tt_res) {
                    // left side
                    if ($tt_res['start_time'] < $busy_s['second_start_time']) {
                        $free = array(
                            'id' => $tt_res['id'],
                            'user_id' => $tt_res['user_id'],
                            'type' => $tt_res['type'],
                            'is_free' => $tt_res['is_free'],
                            'start_time' => $tt_res['start_time'],
                            'end_time' => min($busy_s['second_start_time'], $tt_res['end_time']),
                        );
                        array_push($t_res,$free);
                    }

                    // right side
                    if ($tt_res['end_time'] > $busy_s['second_end_time']) {
                        $free = array(
                            'id' => $tt_res['id'],
                            'user_id' => $tt_res['user_id'],
                            'type' => $tt_res['type'],
                            'is_free' => $tt_res['is_free'],
                            'start_time' => max($busy_s['second_end_time'], $tt_res['start_time']),
                            'end_time' => $tt_res['end_time'],
                        );
                        array_push($t_res,$free);
                    }
                }
            }
            $res = array_merge($res,$t_res);
        }

        foreach ($res as $num => $row) {
            $res[$num]['start_time'] = date("Y-m-d H:i:s", $row['start_time']);
            $res[$num]['end_time'] = date("Y-m-d H:i:s", $row['end_time']);
        }

        return $res;
    }
}