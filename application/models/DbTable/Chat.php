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

    public function getTalks($user) {
        $user_id = $user['id'];
        return $this->_db->fetchAll("
            select max(t.id) as id, t.user_id, u.name, u.lastname, h.when, h.text
            from
            (
            (
            select c.from as user_id, max(c.id) as id
            from chat c
            where
            c.to = $user_id
            group by c.from
            )
            union
            (
            select c.to as user_id, max(c.id) as id
            from chat c
            where
            c.from = $user_id
            group by c.to
            )
            ) as t
            left join users u on t.user_id = u.id
            left join chat h on t.id = h.id
            group by user_id
        ");
    }

}

