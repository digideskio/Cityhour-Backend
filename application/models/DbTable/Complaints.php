<?php

class Application_Model_DbTable_Complaints extends Zend_Db_Table_Abstract
{

    protected $_name = 'complaints';

    public function getTo($id,$type) {
        $res = $this->fetchAll("`to` = $id and `type` = $type");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function addComplaint($data) {
        $this->insert($data);
        return true;
    }

    public function updateComplaint($id,$dscr,$user_id) {
        $this->update(array(
            'dscr' => $dscr
        ),"id = $id and `from` = $user_id");
        return true;
    }

    public function deleteComplaint($user_id,$id) {
        $this->delete("id = $id and `from` = $user_id");
        return true;
    }

}

