<?php


$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

new FindPeople();



$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo 'Page generated in '.$total_time.' seconds.';




class FindPeople {

    /** @var resource $mysqli DB connector */
    var $mysqli;

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


    public function __construct() {
        $this->mainWork();
    }

    public function connect() {
        /**
         * Connect to DB
         */
        $config = parse_ini_file("../application/configs/application.ini");
        $this->mysqli = new mysqli($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password'],$config['resources.db.params.dbname']);
        $this->mysqli->set_charset($config['resources.db.params.charset']);

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            die();
        }
    }

    public function getValues() {
        $this->temp_t = '`'.uniqid().'`';

        $this->b_s = '2013-08-14 09:00:00';
        $this->b_e = '2013-08-14 18:00:00';

        $this->q_s = '2013-08-14 10:00:00';
        $this->q_e = '2013-08-14 19:00:00';

        $this->goal = 2;
        $this->industry = 4;

        $this->n_lat = 50.590798;
        $this->s_lat = 50.2133;
        $this->n_lng = 30.825941;
        $this->s_lng = 30.2394401;

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
        }
    }

    public function createT() {
        $result = $this->mysqli->multi_query("
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
        ");

        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
        if ($result) {
            do {
                if ($result = $this->mysqli->store_result()) {
                    $result->free();
                }
            } while (@$this->mysqli->next_result());
        }


        // Case A Free and Meet not cross
        $this->mysqli->query("
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
        ");
    }

    public function MeetFreeCross() {
        $result = $this->mysqli->query("
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN $this->temp_t mt on t.user_id = mt.user_id
            and mt.type = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time
            where t.type = 1
            order by t.id,mt.start_time
        ");
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function BusyRNotCross() {
        $this->mysqli->query("
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
        ");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
    }

    public function BusyRCross() {
        $result = $this->mysqli->query("
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
        ");
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'zSult');
    }

    public function BusyPlusMeet() {
        $result = $this->mysqli->multi_query("
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
            and ft.id is null; -- not cross meet

            -- not cross busy
            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN $this->temp_t ft on t.user_id = ft.user_id
            and ft.type = 2
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 0
            and t.is_free = 1
            and ft.id is null;

            -- cross
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
        ");

        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
        if ($result) {
            do {
                if ($result = $this->mysqli->store_result()) {
                    $result->free();
                }
            } while (@$this->mysqli->next_result());
        }
    }

    public function SetXNotCross() {
        $this->mysqli->query("
            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN xSult ft on t.user_id = ft.user_id
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 3
            and t.is_free = 1
            and ft.id is null
        ");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
    }

    public function SetXCross() {
        $result = $this->mysqli->query("
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN xSult mt on t.user_id = mt.user_id
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 3
            and t.is_free = 1
            order by t.id,mt.start_time
        ");
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function getResult() {
        $result = $this->mysqli->query("
            select id, user_id, `type`, is_free, start_time, end_time
            from rSult
            where (UNIX_TIMESTAMP(end_time) - UNIX_TIMESTAMP(start_time)) >= 3600
        ");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
        if (!$result) {
            return array();
        }

        $res = array();
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($res,$row);
            }
        }

        return $res;
    }

    public function clearTempData() {

        $this->mysqli->query("drop table $this->temp_t");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            die();
        }

        $this->mysqli->query("drop table rSult");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            die();
        }

        $this->mysqli->query("drop table zSult");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            die();
        }

        $this->mysqli->query("drop table xSult");
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            die();
        }
    }

    public function mainWork() {
        $this->getValues();
        $this->connect();
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

        // Finish!!!
        $result = $this->getResult();
        var_dump($result);

        // Drop temporary data
        $this->clearTempData();

        $this->mysqli->close();
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
        $this->mysqli->query($sql);
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }
        }
        return true;
    }

    public function getFreeArray($result) {
        if ($this->mysqli->errno != 0) {
            echo $this->mysqli->errno . ": " . $this->mysqli->error . "<br><br>";
            $this->clearTempData();
            die();
        }

        if (!$result) {
            return array();
        }


        if ($result->num_rows != 0) {

            $last = array();
            $t_res = array();
            $res = array();
            $busy = array();
            while ($row = $result->fetch_assoc()) {
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

            foreach ($res as $num => $row) {
                $res[$num]['start_time'] = date("Y-m-d H:i:s", $row['start_time']);
                $res[$num]['end_time'] = date("Y-m-d H:i:s", $row['end_time']);
            }

            $result->free();
            return $res;
        }
        else {
            $result->free();
            return array();
        }
    }
}