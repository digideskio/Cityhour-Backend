<?php

class Application_Model_DbTable_Notifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'notifications';


    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("`to` = $user_id and status = 0");
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

    public function getCounters($user) {
        $user_id = $user['id'];
        $notifications = $this->_db->fetchRow("
            select
                (select count(id)
                from notifications
                where `to` = $user_id
                and type in (0,3)
                and status = 0) as requests,
                (select count(id)
                from notifications
                where `to` = $user_id
                and type in (1,2,4,5,6,7,8)
                and status = 0) as notification
          ");
        return array(
            'chat' => (int)$this->_db->fetchOne("select sum(chat) as chat
                                            from
                                            (select count(id) as chat
                                            from chat
                                            where `to` = $user_id
                                            and `status` = 0
                                            GROUP BY `from`) as t"),
            'requests' => (int)$notifications['requests'],
            'notification' => (int)$notifications['notification']
        );
    }


}