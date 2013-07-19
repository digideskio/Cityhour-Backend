<?php

class Application_Model_DbTable_UserContactsWait extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_contacts_wait';

    public function add($data) {
        $this->insert($data);
        return true;
    }

    public function getAll($id) {
        $res = $this->fetchAll("user_id = $id");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else
            return false;
    }

}