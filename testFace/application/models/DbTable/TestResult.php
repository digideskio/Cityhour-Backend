<?php

class Application_Model_DbTable_TestResult extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_result';

    public function getResult($id) {
        return $this->fetchRow("user_id = $id")->toArray();
    }

    public function saveResult($data,$id) {
        unset($data['ok']);
        return $this->update($data,"user_id = $id");
    }

    public function check($id, $start_time, $end_time) {
        $start_time = date('H:i:s',$start_time);
        $end_time = date('H:i:s',$end_time);
        if ($this->fetchRow("is_free = 1 and user_id = $id and start_time = '$start_time' and end_time = '$end_time' ")) {
            return true;
        }
        return false;
    }

}

