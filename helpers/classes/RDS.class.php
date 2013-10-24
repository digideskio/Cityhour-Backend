<?php

class RDS {

    /** @var resource $mysqli DB connector */
    var $mysql;

    /** @var array $config Config */
    var $config;

    /** @var boolean $debug Debug true/false */
    var $debug;


    public function dbConnect($debug) {
        $this->debug = $debug;

        /**
         * Get configs
         */
        $this->config = parse_ini_file("../application/configs/application.ini");
        $host = $this->config['resources.db.params.host'];
        $username = $this->config['resources.db.params.username'];
        $password = $this->config['resources.db.params.password'];
        $dbname = $this->config['resources.db.params.dbname'];

        /**
         * Connect to DB
         */
        try {
            $this->mysql = new PDO("mysql:host=$host;dbname=$dbname", "$username", "$password");
            $this->mysql->exec("SET NAMES utf8, time_zone = '+0:00'");
            if ($this->debug) {
                $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return false;
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    public function query($sql, $fetch, $fetchAll) {
        $result = $this->mysql->query($sql);
        if ($fetch) {
            $result = $result->fetch(PDO::FETCH_ASSOC);
        }
        if ($fetchAll) {
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function startTransaction() {
        $this->mysql->beginTransaction();
    }

    public function commit() {
        $this->mysql->commit();
    }

    public function rollBack() {
        $this->mysql->rollBack();
    }

    public function quote($data) {
        return $this->mysql->quote($data);
    }
}