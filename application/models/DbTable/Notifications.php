<?php

class Application_Model_DbTable_Notifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'notifications';


    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("`to` = $user_id");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function read($id,$user) {
        $user_id = $user['id'];
        $this->update(array(
            'status' => 1
        ),"id = $id and `to` = $user_id");
        return true;
    }


}