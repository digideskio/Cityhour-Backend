<?php

class Application_Model_DbTable_TestResult extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_result';

    public function getResult($id) {
        return $this->fetchRow("user_id = $id")->toArray();
    }

    public function saveResult($data,$id) {
        unset($data['ok']);
        return $this->update($data,"id = $id");
    }

}

