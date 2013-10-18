<?php

include_once 'RDS.class.php';

class Common {

    /** @var object DB class */
    var $db;

    /** @var boolean $debug Debug true/false */
    var $debug;

    /** @var array $data Income paramms */
    var $data;

    /** @var int $start When script start working */
    var $start;

    public function __construct ($debug) {
        if ($debug) {
            $this->debug = $debug;
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $this->start = $time;
        }
        else {
            error_reporting(0);
        }
    }

    public function stopTimer() {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $this->start), 4);
        echo 'Page generated in '.$total_time.' seconds.';
    }

    public function connect() {
        $this->db = new RDS();
        $this->db->dbConnect($this->debug);
    }

    public function query($sql,$fetch = false,$fetchAll = false){
        try {
            return $this->db->query($sql,$fetch,$fetchAll);
        }
        catch(Exception $e)
        {
            $this->answer($e,500);
        }
    }

    public function answer($result,$code) {
        $this->db = null;
        if (!$this->debug) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'body' => $result,
                'errorCode' => $code
            ));
            die();
        }
        else {
            ~r($result);
        }
    }

    public function sortByUser ($data) {
        return usort($data, function ($a, $b) {
            return strcmp($a["user_id"], $b["user_id"]);
        });
    }

}