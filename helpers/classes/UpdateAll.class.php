<?php
include_once 'Common.class.php';

class UpdateAll extends Common {

    /** @var string $b_s Bussines time start */
    /** @var string $b_e Bussines time end */
    /** @var string $q_s Query time start */
    /** @var string $q_e Query time end */
    var $b_s;
    var $b_e;
    var $q_s;
    var $q_e;

    public function __construct($debug) {
        $this->debug = $debug;
        $this->q_s = strtotime('+15 days');
        $this->q_e = strtotime('+16 days');
    }

    public function getAllData() {
        r($this->q_s);
        ~r((string)$this->q_e);

        return $this->query("
            select c.id, c.user_id, GREATEST('$this->q_s', c.start_time), LEAST('$this->q_e', c.end_time), c.type, s.value as is_free, i.lat, i.lng
            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join calendar i on c.id = i.id
            where
            (
                (c.start_time between '$this->q_s' and '$this->q_e') or (c.end_time between '$this->q_s' and '$this->q_e') or (c.start_time >= '$this->q_s' and c.end_time <= '$this->q_e')
            )
            and
            (
                (c.type = 0 and s.value = '1')
                or (c.type = 2 and c.status = 2)
                or c.type = 1
            )
        ",false,true);
    }

}