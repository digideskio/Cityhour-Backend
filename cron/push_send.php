<?php
$db = new databaseClass();
$db->connect();

while (true) {
    $all = $db->getAll();
    if ($all) {
        echo exec('/bin/bash push_send.sh '.$all['ids']);
    }
    else {
        sleep(5);
    }
}


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
    }

    public function disconnect() {
        mysql_close($this->conn);
    }

    public function reconnect() {
        $this->disconnect();
        $this->connect();
    }

    public function getAll() {
        //auto reconnect if MySQL server has gone away
        while (!mysql_ping($this->conn)) {
            sleep(2);
            $this->reconnect();
        }

        $result = mysql_query("select group_concat(id) as ids from push_messages where `status` = 0");
        if (!$result) {
            return false;
        }

        if (mysql_num_rows($result) != 0) {
            $res = mysql_fetch_assoc($result);
            if (!$res['ids']) {
                $res = false;
            }
            mysql_free_result($result);
            return $res;
        }
        else {
            mysql_free_result($result);
            return false;
        }
    }

}
