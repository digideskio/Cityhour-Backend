<?php

class Common {
    var $debug;

    /** @var resource $mysqli DB connector */
    var $mysql;

    /** @var array $config Config */
    var $config;

    /** @var string $temp_t name of temporary table for main select */
    var $temp_t = false;

    /** @var boolean $map Map request or no */
    var $map = false;

    /** @var array $data Income paramms */
    var $data;

    /** @var int $user_id Id of user */
    var $user_id;

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
            if ($this->debug) {
                $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
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
            $this->clearTempData();
            $this->answer($e,500);
            die();
        }
        return $result;
    }

    public function getUser($token) {
        $sql = "
            select *
            from users
            where private_key = '$token'
        ";
        $res = $this->query($sql,true);

        if (isset($res['status']) && $res['status'] == 0) {
            $this->user_id = $res['id'];
            return $res;
        }
        elseif (isset($res['status']) && $res['status'] != 0) {
            $this->answer('Current user blocked',407);
        }
        else {
            $this->answer('Have no permissions',401);
        }
    }

    public function answer($result,$code) {
        $this->mysql = null;
        if (!$this->debug) {
            if (!$this->map) {
                foreach ($result as $num => $row) {
                    $result[$num]['start_time'] = strtotime($row['start_time']);
                    $result[$num]['end_time'] = strtotime($row['end_time']);
                    if (!$row['foursquare_id']) {
                        $result[$num]['foursquare_id'] = null;
                    }
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
        die();
    }

    public function clearTempData() {
        if ($this->temp_t) {
            $sql = "drop table $this->temp_t";
            $this->query($sql);
        }
    }

    public function insertInto($result, $db) {
        if ($result) {
            /** @var $db string Name of DB */
            $sql = "insert into $db (id, user_id, `type`, is_free, start_time, end_time) VALUES ";

            $first = true;
            foreach ($result as $row) {
                if (!$first) {
                    $sql .= ',';
                };
                $first = false;
                $sql .= "(".$row['id'].",".$row['user_id'].",".$row['type'].",".$row['is_free'].",'".$row['start_time']."','".$row['end_time']."')";
            }
            $this->query($sql);
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