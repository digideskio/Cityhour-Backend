<?php

class Application_Model_DbTable_Notifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'notifications';


    public function getAll($user) {
        $user_id = $user['id'];
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        $res = $this->_db->fetchAll("
            (SELECT n.id,
                    n.from,
                    n.to,
                    n.type,
                    n.item,
                    n.text,
                    n.status,
                    Unix_timestamp(n.time)  AS time_when,
                    CASE
                      WHEN (SELECT DISTINCT( f.id )
                            FROM   user_friends f
                            WHERE  f.user_id = n.to
                               AND f.friend_id = n.from
                               AND f.status = 1) > 0 THEN Concat(u.name, ' ', u.lastname)
                      ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                    end                     AS fullname,
                    Concat('$url', u.photo) AS photo,
                    j.name                  AS job,
                    j.company,
                    0 as place
             FROM   notifications n
                    LEFT JOIN users u
                           ON n.from = u.id
                    LEFT JOIN user_jobs j
                           ON u.id = j.user_id
                              AND j.current = 1
                              AND j.type = 0
             WHERE  n.to = $user_id
                AND n.status = 0
                AND n.type IN ( 0, 1, 2, 7, 8 )
             GROUP  BY n.id)
            UNION
            (SELECT n.id,
                    n.from,
                    n.to,
                    n.type,
                    n.item,
                    n.text,
                    n.status,
                    Unix_timestamp(n.time) AS time_when,
                    CASE
                      WHEN c.email = 0 THEN
                        CASE
                          WHEN (SELECT DISTINCT( f.id )
                                FROM   user_friends f
                                WHERE  f.user_id = n.to
                                   AND f.friend_id = n.from
                                   AND f.status = 1) > 0 THEN
                          Concat(u.name, ' ', u.lastname)
                          ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                        end
                      ELSE e.name
                    end                    AS fullname,
                    CASE
                      WHEN c.email = 0 THEN Concat('$url', u.photo)
                      ELSE ''
                    end                    AS photo,
                    j.name                 AS job,
                    j.company,
                    c.place as place
             FROM   notifications n
                    LEFT JOIN calendar c
                           ON n.item = c.id
                    LEFT JOIN users u
                           ON c.user_id = u.id
                              AND c.email = 0
                    LEFT JOIN email_users e
                           ON c.user_id = e.id
                              AND c.email = 1
                    LEFT JOIN user_jobs j
                           ON u.id = j.user_id
                              AND j.current = 1
                              AND j.type = 0
             WHERE  n.to = $user_id
                AND n.status = 0
                AND n.type IN ( 3, 4, 5, 6, 9 )
             GROUP  BY n.id)
        ");
        return $res;
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
                and type in (0,3,9)
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