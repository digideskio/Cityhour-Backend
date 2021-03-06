<?php

class Application_Model_DbTable_Notifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'notifications';


    public function getAll($user,$id,$item) {
        $user_id = $user['id'];
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        if (is_numeric($item)) {
            $res = $this->_db->fetchAll("
                SELECT n.id,
                        n.from,
                        n.to,
                        3 as type,
                        n.item,
                        n.text,
                        n.template,
                        n.action,
                        n.status,
                        c.email,
                        Unix_timestamp(n.time) AS time_when,
                        CASE
                          WHEN c.email = 0 THEN
                            CASE
                              WHEN (SELECT DISTINCT( f.id )
                                    FROM   user_friends f
                                    WHERE  f.user_id = n.to
                                       AND f.friend_id = n.from
                                       AND f.status = 1 limit 1) > 0 THEN
                              Concat(u.name, ' ', u.lastname)
                              ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                            end
                          ELSE e.name
                        end                    AS fullname,
                        CASE
                          WHEN c.email = 0 THEN Concat('$url', u.photo)
                          ELSE ''
                        end                    AS photo,
                        case
                          when j.active = 0 then concat('Former ',j.name)
                          else j.name
                        end as job,
                        j.company,
                        c.place as place,
                        c.foursquare_id as foursquare_id,
                        unix_timestamp(c.start_time) as start_time,
                        unix_timestamp(c.end_time) as end_time,
                        c.offset as offset,
                        c.city_name as city_name,
                        c.goal as goal,
                        c.goal_str as goal_str
                 FROM   notifications n
                        LEFT JOIN calendar c
                               ON n.item = c.id
                        LEFT JOIN users u
                               ON n.to = u.id
                                  AND c.email = 0
                        LEFT JOIN email_users e
                               ON c.user_id_second = e.id
                                  AND c.email = 1
                        LEFT JOIN user_jobs j
                               ON u.id = j.user_id
                                  AND j.current = 1
                                  AND j.type = 0
                 WHERE  n.from = $user_id and n.type in (3,13)
                 and n.item = $item
                 GROUP  BY n.id
                 order by n.id desc
            ");
        }
        else
        {
            if (is_numeric($id)) {
                $id = "and n.id = $id";
            }
            else {
                $id = '';
            }

            $res = $this->_db->fetchAll("
            SELECT *
            FROM (
                (SELECT n.id,
                        n.from,
                        n.to,
                        n.type,
                        n.item,
                        n.text,
                        n.template,
                        n.action,
                        n.status,
                        null as email,
                        Unix_timestamp(n.time)  AS time_when,
                        CASE
                          WHEN (SELECT DISTINCT( f.id )
                                FROM   user_friends f
                                WHERE  f.user_id = n.to
                                   AND f.friend_id = n.from
                                   AND f.status = 1 limit 1) > 0 THEN Concat(u.name, ' ', u.lastname)
                          ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                        end                     AS fullname,
                        Concat('$url', u.photo) AS photo,
                        case
                          when j.active = 0 then concat('Former ',j.name)
                          else j.name
                        end as job,
                        j.company,
                        null as place,
                        null as foursquare_id,
                        null as start_time,
                        null as end_time,
                        null as offset,
                        null as city_name,
                        null as goal,
                        null as goal_str
                 FROM   notifications n
                        LEFT JOIN users u
                               ON n.from = u.id
                        LEFT JOIN user_jobs j
                               ON u.id = j.user_id
                                  AND j.current = 1
                                  AND j.type = 0
                 WHERE  n.to = $user_id
                    AND n.type IN ( 0, 1, 2, 7, 8, 12 )
                    $id
                 GROUP  BY n.id
                 order by n.id desc
                 limit 50
                 )
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
                        c.email,
                        Unix_timestamp(n.time) AS time_when,
                        CASE
                          WHEN c.email = 0 THEN
                            CASE
                              WHEN (SELECT DISTINCT( f.id )
                                    FROM   user_friends f
                                    WHERE  f.user_id = n.to
                                       AND f.friend_id = n.from
                                       AND f.status = 1 limit 1) > 0 THEN
                              Concat(u.name, ' ', u.lastname)
                              ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                            end
                          ELSE e.name
                        end                    AS fullname,
                        CASE
                          WHEN c.email = 0 THEN Concat('$url', u.photo)
                          ELSE ''
                        end                    AS photo,
                        case
                          when j.active = 0 then concat('Former ',j.name)
                          else j.name
                        end as job,
                        j.company,
                        c.place as place,
                        c.foursquare_id as foursquare_id,
                        unix_timestamp(c.start_time) as start_time,
                        unix_timestamp(c.end_time) as end_time,
                        c.offset as offset,
                        c.city_name as city_name,
                        c.goal as goal,
                        c.goal_str as goal_str
                 FROM   notifications n
                        LEFT JOIN calendar c
                               ON n.item = c.id
                        LEFT JOIN users u
                               ON n.from = u.id
                                  AND c.email = 0
                        LEFT JOIN email_users e
                               ON c.user_id_second = e.id
                                  AND c.email = 1
                        LEFT JOIN user_jobs j
                               ON u.id = j.user_id
                                  AND j.current = 1
                                  AND j.type = 0
                 WHERE n.to = $user_id AND ( n.type IN ( 4, 5, 6, 10, 11 ) or (n.type IN ( 3,9 ) and n.status not in (1,4) ) )
                    $id
                 GROUP  BY n.id
                 order by n.id desc
                 limit 50
                 )
                 union
                 (SELECT n.id,
                            n.from,
                            n.to,
                            3 as type,
                            n.item,
                            n.text,
                            n.template,
                            n.action,
                            n.status,
                            c.email,
                            Unix_timestamp(n.time) AS time_when,
                            CASE
                              WHEN c.email = 0 THEN
                                CASE
                                  WHEN (SELECT DISTINCT( f.id )
                                        FROM   user_friends f
                                        WHERE  f.user_id = n.to
                                           AND f.friend_id = n.from
                                           AND f.status = 1 limit 1) > 0 THEN
                                  Concat(u.name, ' ', u.lastname)
                                  ELSE Concat(u.name, ' ', Substr(u.lastname, 1, 1), '.')
                                end
                              ELSE e.name
                            end                    AS fullname,
                            CASE
                              WHEN c.email = 0 THEN Concat('$url', u.photo)
                              ELSE ''
                            end                    AS photo,
                            case
                              when j.active = 0 then concat('Former ',j.name)
                              else j.name
                            end as job,
                            j.company,
                            c.place as place,
                            c.foursquare_id as foursquare_id,
                            unix_timestamp(c.start_time) as start_time,
                            unix_timestamp(c.end_time) as end_time,
                            c.offset as offset,
                            c.city_name as city_name,
                            c.goal as goal,
                            c.goal_str as goal_str
                     FROM   notifications n
                            LEFT JOIN calendar c
                                   ON n.item = c.id
                            LEFT JOIN users u
                                   ON n.to = u.id
                                      AND c.email = 0
                            LEFT JOIN email_users e
                                   ON c.user_id_second = e.id
                                      AND c.email = 1
                            LEFT JOIN user_jobs j
                                   ON u.id = j.user_id
                                      AND j.current = 1
                                      AND j.type = 0
                     WHERE  n.from = $user_id and n.type in (3,13) and n.status not in (2,4)
                        $id
                     GROUP  BY n.id
                     order by n.id desc
                     limit 50
                 )
             ) t
             ORDER BY t.id DESC
            ");
        }
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

    public function getSumCounters($user_id) {
        $not = $this->_db->fetchOne("
            select count(id)
                from notifications
                where `to` = $user_id
                and type in (0,3,9,1,4,5,6,7,8)
                and status = 0
        ");
        $chat = $this->_db->fetchOne("
            select count(chat) as chat
                from
                (select count(id) as chat
                from chat
                where `to` = $user_id
                and `status` = 0
                GROUP BY `from`) as t");
        return (int)($not + $chat);
    }


}