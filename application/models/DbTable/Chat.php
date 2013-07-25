<?php

class Application_Model_DbTable_Chat extends Zend_Db_Table_Abstract
{

    protected $_name = 'chat';


    public function getMessages($user,$from) {
        $user_id = $user['id'];
        $res = $this->fetchAll("(`from` = $from and `to` = $user_id) or (`from` = $user_id and `to` = $from)");
        if ($res != null) {
            $res = $res->toArray();
            foreach ($res as $num => $row) {
                $res[$num]['when'] = strtotime($row['when']);
            }
            return $res;
        }
        else {
            return false;
        }
    }

}

