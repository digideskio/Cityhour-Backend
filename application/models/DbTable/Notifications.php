<?php

class Application_Model_DbTable_Notifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'notifications';


    public function getAll($user,$id) {
        $user_id = $user['id'];
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        if (is_numeric($id)) {
            $id = "and n.id = $id";
        }
        else {
            $id = '';
        }

        $res = $this->_db->fetchAll("
        select *
        from (
                (SELECT n.id,
                        n.from,
                        n.to,
                        n.type,
                        n.item,
                        n.text,
                        n.template,
                        n.action,
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
                        null as place,
                        null as foursquare_id,
                        null as start_time
                 FROM   notifications n
                        LEFT JOIN users u
                               ON n.from = u.id
                        LEFT JOIN user_jobs j
                               ON u.id = j.user_id
                                  AND j.current = 1
                                  AND j.type = 0
                 WHERE  n.to = $user_id
                    AND n.type IN ( 0, 1, 2, 7, 8 )
                    $id
                 GROUP  BY n.id)
                UNION
                (SELECT n.id,
                        n.from,
                        n.to,
                        n.type,
                        n.item,
                        n.text,
                        n.template,
                        n.action,
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
                        c.place as place,
                        c.foursquare_id as foursquare_id,
                        unix_timestamp(c.start_time) as start_time
                 FROM   notifications n
                        LEFT JOIN calendar c
                               ON n.item = c.id
                        LEFT JOIN users u
                               ON n.from = u.id
                                  AND c.email = 0
                        LEFT JOIN email_users e
                               ON c.user_id = e.id
                                  AND c.email = 1
                        LEFT JOIN user_jobs j
                               ON u.id = j.user_id
                                  AND j.current = 1
                                  AND j.type = 0
                 WHERE  n.to = $user_id
                    AND n.type IN ( 3, 4, 5, 6, 9 )
                    $id
                 GROUP  BY n.id)
                ) as tn
                limit 50
        ");
        return $res;
    }

    public function read($id,$user) {
        $user_id = $user['id'];
        $id = explode(',',$id);

        $idc = array();
        foreach ($id as $row) {
            if (is_numeric($row)) {
                array_push($idc,$row);
            }
        }

        if ($idc) {
            $idc = implode(',',$idc);
            $this->update(array(
                'status' => 1
            ),"id in ($idc) and `to` = $user_id");
        }
        return true;
    }

    public function getCounters($user) {
        $user_id = $user['id'];
        $notifications = $this->_db->fetchRow("
            select
                (select count(id)
                from notifications
                where `to` = $user_id
                and type in (0)
                and status = 0) as contacts,
                (select count(id)
                from notifications
                where `to` = $user_id
                and type in (3,9)
                and status = 0) as meetings,
                (select count(id)
                from notifications
                where `to` = $user_id
                and type in (1,4,5,6,7,8)
                and status = 0) as system
          ");
        return array(
            'chat' => (int)$this->_db->fetchOne("select count(chat) as chat
                                            from
                                            (select count(id) as chat
                                            from chat
                                            where `to` = $user_id
                                            and `status` = 0
                                            GROUP BY `from`) as t"),
            'contacts' => (int)$notifications['contacts'],
            'system' => (int)$notifications['system'],
            'meetings' => (int)$notifications['meetings']
        );
    }

    public function insertNotification($data) {
        return $this->insert($data);
    }


}