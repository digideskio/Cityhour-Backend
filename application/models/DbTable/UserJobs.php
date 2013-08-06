<?php

class Application_Model_DbTable_UserJobs extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_jobs';

    public function getJobs($id) {
        return $this->fetchAll("user_id = $id and `type` = 0")->toArray();
    }

    public function getEducation($id) {
        return $this->fetchAll("user_id = $id and `type` = 1")->toArray();
    }

}

