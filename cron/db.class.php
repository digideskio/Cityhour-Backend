<?php

class DBcron {

    /** @var resource $mysql DB connector */
    var $mysql;

    /** @var array $config Config */
    var $config;

    public function __construct() {
        $this->connect();
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

}