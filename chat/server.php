<?php

$socket = stream_socket_server("tcp://0.0.0.0:3000", $errno, $err) or die($err);
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
    }

    public function disconnect() {
        mysql_close($this->conn);
    }

    public function reconnect() {
        $this->disconnect();
        $this->connect();
    }

    public function storeMSG($from,$to,$msg) {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }

        $msg = mysql_real_escape_string($msg);
        $to = mysql_real_escape_string($to);
        $from = mysql_real_escape_string($from);
        $result = mysql_query("insert into chat (`to`,`from`,`text`) values ('$to','$from','$msg')");

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

                    // notify other users
                    //foreach ($conns as $i => $c) {
                    //    if ($i != 0) fwrite($c, "data: " . $user . " has joined.\n\n");
                    //}

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
                // user/browser closed connection
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
                    // notify other users
//                    foreach ($conns as $i => $c) {
//                        if ($i != 0) fwrite($c, "data: " . $user . " has left.\n\n");
//                    }
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

                                $id = $db_class->storeMSG($user_all['id'],$target_all['id'],$recv['text']);
                                if ($id) {
                                    $msg_data = array(
                                        'from' => $user_all['id'],
                                        'id' => $id,
                                        'text' => $recv['text'],
                                        'to' => $target_all['id'],
                                        'when' => time()
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