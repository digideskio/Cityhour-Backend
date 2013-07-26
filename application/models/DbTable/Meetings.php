<?php

class Application_Model_DbTable_Meetings extends Zend_Db_Table_Abstract
{

    protected $_name = 'meetings';

    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("user_id = $user_id or user_id_second = $user_id");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function addMeet($data) {
        $this->insert($data);
        return true;
    }

    public function updateMeet($user,$data,$id) {
        $user_id = $user['id'];
        $this->update($data,"(user_id = $user_id or user_id_second = $user_id) and id = $id");
        return true;
    }

    public function deleteMeet($user,$id) {
        $user_id = $user['id'];
        $this->delete("id = $id and (user_id = $user_id or user_id_second = $user_id)");
        return true;
    }

}

