<?php

class Application_Model_DbTable_Calendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'calendar';

    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("user_id = $user_id");

        foreach ($res as $num=>$row) {
            $row['start_time'] = strtotime($row['start_time']);
            $row['end_time'] = strtotime($row['end_time']);
            $row['time_create'] = strtotime($row['time_create']);

            $res[$num] = $row;
        }

        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function getFreeArray($result) {
        if (!$result) {
            return array();
        }

        $last = array();
        $t_res = array();
        $res = array();
        $busy = array();
        foreach ($result as $row) {
            if (!$last) {
                array_push($busy,$row);
            }
            else {
                if ($last['id'] != $row['id']) {

                    $t_res[0] = array(
                        'id' => $last['id'],
                        'user_id' => $last['user_id'],
                        'type' => $last['type'],
                        'is_free' => $last['is_free'],
                        'start_time' => $last['start_time'],
                        'end_time' => $last['end_time'],
                    );

                    foreach ($busy as $busy_s) {
                        $l_res = $t_res;
                        $t_res = array();
                        foreach ($l_res as $tt_res) {
                            // left side
                            if ($tt_res['start_time'] <= $busy_s['second_start_time']) {
                                $free = array(
                                    'id' => $tt_res['id'],
                                    'user_id' => $tt_res['user_id'],
                                    'type' => $tt_res['type'],
                                    'is_free' => $tt_res['is_free'],
                                    'start_time' => $tt_res['start_time'],
                                    'end_time' => min($busy_s['second_start_time'], $tt_res['end_time']),
                                );
                                array_push($t_res,$free);
                            }

                            // right side
                            if ($tt_res['end_time'] >= $busy_s['second_end_time']) {
                                $free = array(
                                    'id' => $tt_res['id'],
                                    'user_id' => $tt_res['user_id'],
                                    'type' => $tt_res['type'],
                                    'is_free' => $tt_res['is_free'],
                                    'start_time' => max($busy_s['second_end_time'], $tt_res['start_time']),
                                    'end_time' => $tt_res['end_time'],
                                );
                                array_push($t_res,$free);
                            }
                        }
                    }

                    $busy = array();
                    array_push($busy,$row);

                    $res = array_merge($res,$t_res);
                    $t_res = array();
                }
                else {
                    array_push($busy,$row);
                }
            }
            $last = $row;
        }

        if (isset($last['id'])) {
            $t_res[0] = array(
                'id' => $last['id'],
                'user_id' => $last['user_id'],
                'type' => $last['type'],
                'is_free' => $last['is_free'],
                'start_time' => $last['start_time'],
                'end_time' => $last['end_time'],
            );

            foreach ($busy as $busy_s) {
                $l_res = $t_res;
                $t_res = array();
                foreach ($l_res as $tt_res) {
                    // left side
                    if ($tt_res['start_time'] < $busy_s['second_start_time']) {
                        $free = array(
                            'id' => $tt_res['id'],
                            'user_id' => $tt_res['user_id'],
                            'type' => $tt_res['type'],
                            'is_free' => $tt_res['is_free'],
                            'start_time' => $tt_res['start_time'],
                            'end_time' => min($busy_s['second_start_time'], $tt_res['end_time']),
                        );
                        array_push($t_res,$free);
                    }

                    // right side
                    if ($tt_res['end_time'] > $busy_s['second_end_time']) {
                        $free = array(
                            'id' => $tt_res['id'],
                            'user_id' => $tt_res['user_id'],
                            'type' => $tt_res['type'],
                            'is_free' => $tt_res['is_free'],
                            'start_time' => max($busy_s['second_end_time'], $tt_res['start_time']),
                            'end_time' => $tt_res['end_time'],
                        );
                        array_push($t_res,$free);
                    }
                }
            }
            $res = array_merge($res,$t_res);
        }

        foreach ($res as $num => $row) {
            $res[$num]['start_time'] = date("Y-m-d H:i:s", $row['start_time']);
            $res[$num]['end_time'] = date("Y-m-d H:i:s", $row['end_time']);
        }

        return $res;
    }

    public function getFreeSlots($user, $n_id) {
        $this->_db->beginTransaction();
        try {
            $this->_db->query("
                create temporary table rSult (`id` bigint(20) unsigned DEFAULT NULL,
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
                `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
                `start_time` timestamp NULL DEFAULT NULL,
                `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
            ");
            $this->_db->query("
                insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
                (
                    select t.id, t.user_id, t.type, 0 as is_free, t.start_time, t.end_time
                    from calendar t
                    left outer JOIN calendar mt on t.user_id = mt.user_id
                    and mt.type = 2
                    and mt.status = 2
                    and t.start_time <= mt.end_time
                    and t.end_time >= mt.start_time

                    where t.type = 1
                    and t.user_id = $n_id
                    and t.start_time >= now()
                    and mt.id is null
                )
            ");

            $cross = $this->_db->fetchAll("
                select t.id, t.user_id, t.type, 0 as is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
                from calendar t
                inner JOIN calendar mt on t.user_id = mt.user_id
                and mt.type = 2
                and mt.status = 2
                and t.start_time <= mt.end_time
                and t.end_time >= mt.start_time

                where t.type = 1
                and t.user_id = $n_id
                and t.start_time >= now()
                order by t.id,mt.start_time
            ");
            $free_cross = $this->getFreeArray($cross);

            foreach ($free_cross as $row) {
                $this->_db->insert('rSult',$row);
            }

            $res = $this->_db->fetchAll("
                select r.id, r.start_time, r.end_time, c.foursquare_id, c.place, c.lat, c.lng
                from rSult r
                left join calendar c on r.id = c.id
                group by r.id, r.start_time
            ");
            $this->_db->query("drop table rSult");

            $this->_db->commit();
            return array(
                'body' => $res,
                'code' => 200
            );
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return array(
                'body' => $e,
                'code' => 500
            );
        }
    }

    public function getSlot($sid,$user_id) {
        $res = $this->fetchRow("id = $sid and user_id = $user_id");
        if ($res) {
            $res = $res->toArray();

            if ($res['type'] = 1 && strtotime($res['end_time']) > time()) {
                return $res;
            }
            elseif ($res['type'] = 2 && $res['status'] = 2 && strtotime($res['end_time']) < time()) {
                return $res;
            }
        }
        return false;
    }

    public function prepeareSlotCreate($user,$data,$type=0,$status=0,$user_second = false, $email = false) {
        $res['start_time'] = date('Y-m-d H:i:s',(int)$data['date_from']);
        $res['end_time'] = date('Y-m-d H:i:s',(int)$data['date_to']);
        $res['user_id'] = $user['id'];
        $res['type'] = $type;
        $res['status'] = $status;
        $res = array_merge($res,Application_Model_Common::getCity($data['city']));

        if (isset($data['foursquare_id']) && $data['foursquare_id']) {
            $place = Application_Model_Common::getPlace($data['foursquare_id']);
            $res = array_merge($res,$place);
        }

        if (isset($data['goal']) && is_numeric($data['goal'])) {
            $res['goal'] = $data['goal'];
        }

        if (is_numeric($user_second)) {
            $res['user_id_second'] = $user_second;
        }

        if ($email) {
            $res['email'] = 1;
        }

        return $res;
    }

    public function createMeetingEmail($user,$data) {
        if (isset($data['person_value']) && $data['person_value'] && isset($data['person_name']) && $data['person_name']) {
            $email = $data['person_value'];
            $che = $this->_db->fetchOne("
                select id
                from users
                where email = '$email' and status = 0
            ");

            if (is_numeric($che)) {
                $data['person_value'] = $che;
                return $this->createMeeting($user,$data);
            }

            $key = uniqid(sha1(time()), false);
            $db = new Application_Model_DbTable_EmailUsers();
            $user_second = $db->addUserEmail(array(
                'name' => $data['person_name'],
                'email' => $data['person_value'],
                'key' => $key
            ));
        }
        else {
            return 400;
        }

        try {
            $this->_db->beginTransaction();
            $data = $this->prepeareSlotCreate($user,$data,2,1,$user_second,true);
            $this->insert($data);


            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->email->url;

            if (isset($data['place'])) {
                $place = ' в '.$data['place'];
            }
            else {
                $place = '';
            }
            $options = array(
                'name' => $user['name'],
                'lastname' => substr($user['lastname'], 0, 1),
                'time' => $data['start_time'],
                'place' => $place,
                'url_ok' => $url.'meetings/?answer=4&key='.$key,
                'url_nok' => $url.'meetings/?answer=5&key='.$key
            );
            Application_Model_Common::sendEmail($email, "Реквест Митинг!", null, null, null, "meeting_request.phtml", $options, 'meeting_request');


            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
    }

    public function createFreeSlot($user,$data) {
        try {
            $this->_db->beginTransaction();
            $data = $this->prepeareSlotCreate($user,$data,1);
            $this->insert($data);
            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
    }

    public function createMeeting($user,$data) {
        if (isset($data['person_value']) && is_numeric($data['person_value'])) {
            $uid = $data['person_value'];
            $user_second = $this->_db->fetchOne("
                select id
                from users
                where id = $uid and status = 0
            ");

            if (!is_numeric($user_second)) {
                return 408;
            }
        }

        $q_in = $data['date_from'];
        $q_out = $data['date_to'];

        $che = $this->_db->fetchOne("
            select c.id
            from calendar c
            where
            (unix_timestamp(c.start_time) between $q_in and $q_out or unix_timestamp(c.end_time) between $q_in and $q_out or (unix_timestamp(c.start_time) >= $q_in and unix_timestamp(c.end_time) >= $q_out) )
            and c.user_id = $user_second
            and c.type = 2
            and c.status = 2
            limit 1
        ");

        if (is_numeric($che)) {
            return 300;
        }

        try {
            $this->_db->beginTransaction();
            $data = $this->prepeareSlotCreate($user,$data,2,1,$user_second);
            $this->insert($data);

            if (isset($data['place'])) {
                $place = ' в '.$data['place'];
            }
            else {
                $place = '';
            }

            $text = $user['name'].' '.substr($user['lastname'], 0, 1).'. пригласил вас на встречу '.$data['start_time'].$place;
            $this->_db->insert('notifications',array(
                'from' => $user['id'],
                'to' => $user_second,
                'type' => 3,
                'text' => $text
            ));

            $push = new Application_Model_DbTable_Push();
            $push->sendPush($user_second,$text,0,array(
                'from' => $user['id'],
                'to' => $user_second,
                'type' => 0
            ));

            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
    }

    public function addSlots($data,$user) {
        if (isset($data['slots'])) {
            $slots = $data['slots'];
            $user_id = $user['id'];

            $old_slots = $this->_db->fetchOne("
            select group_concat(`hash`)
            from calendar
            where user_id = $user_id
        ");
            $che_slots = explode(',',$old_slots);
            $old_slots = array();
            foreach ($slots as $num => $row ) {
                if (!in_array($row['hash'],$che_slots) && !in_array($row['hash'],$old_slots)) {
                    $row['user_id'] = $user['id'];
                    $row['start_time'] = date('Y-m-d H:i:s',(int)$row['start_time']);
                    $row['end_time'] = date('Y-m-d H:i:s',(int)$row['end_time']);
                    unset($row['private_key']);

                    $this->insert($row);
                }

                if (!in_array($row['hash'],$old_slots)) {
                    array_push($old_slots,$row['hash']);
                }
            }

            $old_slots = "'".implode("','",$old_slots)."'";
            if ($old_slots) {
                $this->delete("user_id = $user_id and `hash` not in ($old_slots)");
            }
        }
        return true;
    }

    public function prepeareUpdate($data,$slot = false) {
        $res = array();

        if ($slot) {
            if (!isset($data['date_from'])) {
                $data['date_from'] = strtotime($slot['start_time']);
            }

            if (!isset($data['date_to'])) {
                $data['date_to'] = strtotime($slot['end_time']);
            }
            $res = array_merge($slot,$data);
        }
        else {
            if (isset($data['date_from'])) {
                $res['start_time'] = date('Y-m-d H:i:s',(int)$data['date_from']);
            }

            if (isset($data['date_to'])) {
                $res['end_time'] = date('Y-m-d H:i:s',(int)$data['date_to']);
            }

            if (isset($data['goal']) && is_numeric($data['goal'])) {
                $res['goal'] = $data['goal'];
            }

            if (isset($data['city']) && $data['city']) {
                $place = Application_Model_Common::getCity($data['city']);
                $res = array_merge($res,$place);
            }

            if (isset($data['foursquare_id']) && $data['foursquare_id']) {
                $place = Application_Model_Common::getPlace($data['foursquare_id']);
                $res = array_merge($res,$place);
            }

        }

        return $res;
    }

    public function updateSlot($data,$slot,$user) {
        if ($slot['type'] === '2' && isset($data['rating']) && is_numeric($data['rating'])) {
            $sid = $slot['id'];
            $this->update(array(
                'rating' => $data['rating']
            ),"id = $sid");
            return 200;
        }
        elseif ($slot['type'] === true && isset($data['person']) && is_numeric($data['person'])) {
            $res = 400;
            $sid = $slot['id'];

            if ($data['person'] == 0) {
                $data = $this->prepeareUpdate($data);
                if ($data) {
                    $this->update($data,"id = $sid");
                }
                return 200;
            }
            elseif ($data['person'] == 1) {
                $data = $this->prepeareUpdate($data,$slot);
                $res = $this->createMeeting($user,$data);
            }
            elseif ($data['person'] == 2) {
                $data = $this->prepeareUpdate($data,$slot);
                $res = $this->createMeetingEmail($user,$data);
            }

            if ($res == 200) {
                $this->delete("id = $sid");
            }

            return $res;
        }

        return false;
    }

    public function deleteSlot($user,$id) {
        $user_id = $user['id'];
        $this->delete("id = $id and user_id = $user_id");
        return true;
    }
}

