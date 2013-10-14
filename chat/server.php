<?php

//{ "type":"login", "private_key":"b4c43dd7e81870bfe42809cfc9ee686e551a51a751ee811062b7d" }
//{ "type":"msg", "private_key":"b4c43dd7e81870bfe42809cfc9ee686e551a51a751ee811062b7d", "to":"31", "text":"Bla bla" }

include_once("../application/models/Texts.php");
date_default_timezone_set("UTC");
$socket = stream_socket_server("tcp://0.0.0.0:3333", $errno, $err) or die($err);
$conns = array($socket);
$conn_ids = array(0);
$conn_user = array();
$msgs = array();

class databaseClass {
    var $conn;
    var $db;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        $config = parse_ini_file("../application/configs/application.ini");
        $this->conn = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']);
        $this->db = mysql_select_db($config['resources.db.params.dbname']);
        mysql_set_charset('utf8',$this->conn);
        mysql_query("SET NAMES utf8, time_zone = '+0:00'");
    }

    public function disconnect() {
        mysql_close($this->conn);
    }

    public function reconnect() {
        $this->disconnect();
        $this->connect();
    }

    public function sendPush($from,$to,$msg,$user_all = false) {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }

        $che = mysql_query("
            select `value`
            from user_settings
            where user_id = $to and `name` = 'newMessageSync'
        ");

        if ((int)mysql_fetch_assoc($che)['value'] != 1) {
            return true;
        }

        $text = Application_Model_Texts::push()[5];
        $data = json_encode(array(
            'from' => $from,
            'type' => 5
        ));
        $result = mysql_query("insert into push_messages (`user_id`, `type`, `alert`, `data`) values ('$to','5','$text','$data')");

        echo mysql_error();
        if (!$result) {
            return false;
        }

        return true;
    }

    public function storeMSG($when,$from,$to,$msg,$status) {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }
        $msg = mysql_real_escape_string($msg);
        $to = mysql_real_escape_string($to);
        $from = mysql_real_escape_string($from);
        $when = date("Y-m-d H:i:s", $when);
        $result = mysql_query("insert into chat (`when`,`to`,`from`,`text`,`status`) values ('$when','$to','$from','$msg','$status')");

        echo mysql_error();
        if (!$result) {
            return false;
        }
        return mysql_insert_id();
    }

    public function checkUser($token) {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }

        $result = mysql_query("select * from users where private_key = '$token'");
        if (!$result) {
            return false;
        }

        if (mysql_num_rows($result) != 0) {
            $res = mysql_fetch_assoc($result);
            mysql_free_result($result);
            return $res;
        }
        else {
            mysql_free_result($result);
            return false;
        }
    }

    public function getTarget($target) {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }

        $result = mysql_query("select * from users where id = $target");
        if (!$result) {
            return false;
        }

        if (mysql_num_rows($result) != 0) {
            $res = mysql_fetch_assoc($result);
            mysql_free_result($result);
            return $res;
        }
        else {
            mysql_free_result($result);
            return false;
        }
    }
}


$db_class = new databaseClass();


function sendData($conn,$answer) {
    $answer = json_encode($answer);
    fwrite($conn, $answer);
    return true;
}

function login ($recv) {
    $recv = @json_decode($recv);
    if ($recv) {
        $recv = (array)$recv;
        if ($recv['type'] == 'login') {
            return true;
        }
    }
    return false;
}

// server loop
while (true) {
    $reads = $conns;
    // get number of connections with new data
    $mod = stream_select($reads, $write, $except, 5);
    if ($mod === false) break;

    foreach ($reads as $read) {
        if ($read === $socket) {
            $conn = stream_socket_accept($socket);
            $recv = fread($conn, 1024);
            if (empty($recv)) continue;

            if (login($recv)) {
                $recv = @json_decode($recv);
                $recv = (array)$recv;
                $user_all = $db_class->checkUser($recv['private_key']);
                if ($user_all) {
                    $user = $user_all['private_key'];

                    $conns[] = $conn;
                    $conn_ids[] = $user;
                    $conn_user[$user][] = $conn;

                    sendData($conn,array(
                        'connected' => true
                    ));
                    echo "connect " . $user_all['email'] . " from " . stream_socket_get_name($conn, true) . "\n";
                }
                else {
                    sendData($conn,array(
                        'connected' => false
                    ));
                }
            }
            else {
                sendData($conn, array(
                    'status' => 'work'
                ));
                stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
            }
        } else {
            $data = fread($read, 1024);
            if ($data == "" or $data === false) {
                // user closed connection
                if ($data !== false) stream_socket_shutdown($read, STREAM_SHUT_RDWR);
                $conn_id = array_search($read, $conns, true);
                unset($conns[$conn_id]);

                // unregister connection for user
                $user = $conn_ids[$conn_id];
                unset($conn_ids[$conn_id]);
                $conn_id = array_search($read, $conn_user[$user], true);
                unset($conn_user[$user][$conn_id]);

                if (empty($conn_user[$user])) {
                    unset($conn_user[$user]);
                }
            }
            else {
                $recv = json_decode($data);
                if ($recv) {
                    $recv = (array)$recv;
                    if ($recv['type'] == 'msg') {
                        $user_all = $db_class->checkUser($recv['private_key']);
                        if ($user_all) {
                            $target_all = $db_class->getTarget($recv['to']);
                            if ($target_all) {
                                $user = $user_all['private_key'];
                                $target = $target_all['private_key'];
                                echo ($user_all['email'].' sent '.$recv['text'].' to '.$target_all['email'])."\n";

                                $when = time();
                                if (isset($conn_user[$target])) {
                                    $id = $db_class->storeMSG($when,$user_all['id'],$target_all['id'],$recv['text'],1);
                                }
                                else {
                                    $db_class->sendPush($user_all['id'],$target_all['id'],$recv['text'],$user_all);
                                    $id = $db_class->storeMSG($when,$user_all['id'],$target_all['id'],$recv['text'],0);
                                }
                                if ($id) {
                                    $msg_data = array(
                                        'from' => $user_all['id'],
                                        'id' => $id,
                                        'text' => $recv['text'],
                                        'to' => $target_all['id'],
                                        'when' => $when
                                    );
                                    if (isset($conn_user[$target])) {
                                        // send message to one user and to the originator
                                        if ($target != $user) foreach ($conn_user[$target] as $conn) {
                                            sendData($conn,$msg_data);
                                        }
                                        if (isset($conn_user[$user])) foreach ($conn_user[$user] as $conn) {
                                            sendData($conn,$msg_data);
                                        }

                                    } else {
                                        foreach ($conn_user[$user] as $conn) {
                                            sendData($conn,$msg_data);
                                        }
                                    }
                                }
                                else {
                                    sendData($conn,array(
                                        'sent' => false
                                    ));
                                }
                            }
                            else {
                                sendData($conn,array(
                                    'sent' => false
                                ));
                            }
                        }
                        else {
                            sendData($conn,array(
                                'sent' => false
                            ));
                        }
                    }
                }
            }
        }
    }
}