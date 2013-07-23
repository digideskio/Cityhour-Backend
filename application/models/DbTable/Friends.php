<?php

class Application_Model_DbTable_Friends extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_friends';


    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->_db->fetchAll("
            select u.*
            from user_friends f
            left join users u on f.friend_id = u.id
            where f.user_id = $user_id
            and f.status = 1
        ");
        if ($res != null) {
            return $res;
        }
        else {
            return array();
        }
    }

    public function deleteFriends($user,$id) {
        $user_id = $user['id'];
        $data = array(
            'status' => 2
        );
        $this->update($data,"(user_id = $user_id and friend_id = $id) or (user_id = $id and friend_id = $user_id)");
        return true;
    }

}
