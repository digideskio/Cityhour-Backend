<?php

class Application_Model_DbTable_Calendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'calendar';

    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("user_id = $user_id");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function addSlot($data) {
        $this->insert($data);
        return true;
    }

    public function updateSlot($user,$data,$id) {
        $user_id = $user['id'];
        $this->update($data,"id = $id and user_id = $user_id");
        return true;
    }

    public function deleteSlot($user,$id) {
        $user_id = $user['id'];
        $this->delete("id = $id and user_id = $user_id");
        return true;
    }
}

