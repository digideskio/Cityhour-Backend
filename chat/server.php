<?php

$socket = stream_socket_server("tcp://0.0.0.0:3000", $errno, $err) or die($err);
$conns = array($socket);
$conn_ids = array(0);
$conn_user = array();
$msgs = array();
connectDB();

function sendData($conn,$answer) {
    $answer = json_encode($answer);
    fwrite($conn, $answer);
    return true;
}

function connectDB(){
    $config = parse_ini_file("../application/configs/application.ini");
    $db = mysql_connect($config['resources.db.params.host'], $config['resources.db.params.username'], $config['resources.db.params.password']) or
    die("Could not connect: " . mysql_error());
    mysql_select_db($config['resources.db.params.dbname']);
    return $db;
}

function storeMSG($from,$to,$msg,$read) {
    $msg = mysql_real_escape_string($msg);
    $to = mysql_real_escape_string($to);
    $from = mysql_real_escape_string($from);
    $result = mysql_query("insert into chat (`to`,`from`,`text`, `read`) values ('$to','$from','$msg',$read)");
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

function checkUser($token) {
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

function getTarget($target) {
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

function login ($conn, $recv) {
    $recv = @json_decode($recv);
    if ($recv) {
        $recv = (array)$recv;
        if ($recv['type'] == 'login') {
            $user_all = checkUser($recv['private_key']);
            if ($user_all) {
                echo "connect " . $user_all['email'] . " from " . stream_socket_get_name($conn, true) . "\n";

                sendData($conn,array(
                    'connected' => true
                ));

                // register connection in pool
                $conns[] = $conn;
                $conn_ids[] = $user_all['private_key'];

                // allow multiple connections for 1 user
                $conn_user[$user_all['private_key']][] = $conn;
                return true;
            }
        }
    }
    sendData($conn,array(
        'connected' => false
    ));
    return false;
}

function getMessage($conn,$recv) {
    $recv = @json_decode($recv);
    if ($recv) {
        $recv = (array)$recv;
        if ($recv['type'] == 'msg') {
            $user_all = checkUser($recv['private_key']);
            if ($user_all) {
                $target_all = getTarget($recv['to']);
                if ($target_all) {
                    echo ($user_all['email'].' sent '.$recv['msg'].' to '.$target_all['email']);
                    storeMSG($user_all['id'],$target_all['id'],$recv['msg'],1);
                    if (isset($conn_user[$target_all['private_key']])) {
                        // send message to one user and to the originator
                        if ($target_all['private_key'] != $user_all['private_key']) foreach ($conn_user[$target_all['private_key']] as $conn) {
                            sendData($conn,array(
                                'text' => $recv['msg']
                            ));
                        }
                        if (isset($conn_user[$user_all['private_key']])) foreach ($conn_user[$user_all['private_key']] as $conn) {
                            sendData($conn,array(
                                'sent' => true,
                                'online' => true
                            ));
                        }

                    } else {
                        storeMSG($user_all['id'],$target_all['id'],$recv['msg'],0);
                        foreach ($conn_user[$user_all['private_key']] as $conn) {
                            sendData($conn,array(
                                'sent' => true,
                                'online' => false
                            ));
                        }
                    }
                    return true;
                }
            }
        }
    }
    sendData($conn,array(
        'sent' => false
    ));
    return false;
};

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

            if (strpos($recv, "GET / ") === 0) {
                sendData($conn, array(
                    'status' => 'work'
                ));
                stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
            }
            else if (login($conn,$recv)) {

                // notify other users
//                    foreach ($conns as $i => $c) {
//                        if ($i != 0) fwrite($c, "data: " . $user . " has left.\n\n");
//                    }

            }
            else {
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
                $recv = @json_decode($data);
                if ($recv) {
                    getMessage($read,$recv);
                }
            }
        }
    }
}