<?php

class Application_Model_DbTable_Calendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'calendar';

    public function getAll($user) {
        $user_id = $user['id'];
        $start = date('Y-m-d H:i:s',time()-86400);

        $res = $this->fetchAll("
        user_id = $user_id and (
        (`status` = 2 and type = 2) or end_time > '$start'
        )
        ")->toArray();

        foreach ($res as $num=>$row) {
            $row['start_time'] = strtotime($row['start_time']);
            $row['end_time'] = strtotime($row['end_time']);
            $row['time_create'] = strtotime($row['time_create']);
            $res[$num] = $row;
        }

        return $res;
    }

    public function getSlot($sid, $user_id, $meet_notStart = false) {
        $res = $this->fetchRow("id = $sid and user_id = $user_id");
        if ($res) {
            $res = $res->toArray();

            if ($res['type'] = 1 && strtotime($res['end_time']) > time()) {
                return $res;
            }
            elseif (!$meet_notStart && $res['type'] = 2 && $res['status'] = 2 && strtotime($res['end_time']) < time()) {
                return $res;
            }
            elseif ($meet_notStart && $res['status'] = 2 && strtotime($res['start_time']) > time()-3600) {
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
            $data = $this->prepeareSlotCreate($user,$data,1);
            if ($this->haveFreeSlot($data['start_time'],$data['end_time'],$user['id'])){
                return 300;
            }
            $this->_db->beginTransaction();
            $this->insert($data);
            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
    }

    public function haveFreeSlot($q_in,$q_out,$user_id) {
        $che = $this->_db->fetchOne("
            select c.id
            from calendar c
            where
            (unix_timestamp(c.start_time) between $q_in and $q_out or unix_timestamp(c.end_time) between $q_in and $q_out or (unix_timestamp(c.start_time) >= $q_in and unix_timestamp(c.end_time) >= $q_out) )
            and c.user_id = $user_id
            and c.type = 1
            and c.status = 0
            limit 1
        ");
        if (is_numeric($che)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function userBusyOrFree($q_in,$q_out,$user_id) {
        $che = $this->_db->fetchOne("
            select c.id
            from calendar c
            where
            (unix_timestamp(c.start_time) between $q_in and $q_out or unix_timestamp(c.end_time) between $q_in and $q_out or (unix_timestamp(c.start_time) >= $q_in and unix_timestamp(c.end_time) >= $q_out) )
            and c.user_id = $user_id
            and c.type = 2
            and c.status = 2
            limit 1
        ");
        if (is_numeric($che)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function createMeeting($user,$data) {


        if (isset($data['person_value']) && is_numeric($data['person_value']) && !Application_Model_DbTable_Users::isValidUser($data['person_value'])) {
            $user_second = $data['person_value'];
        }
        else {
            return 408;
        }

        if ($this->userBusyOrFree($data['date_from'],$data['date_to'],$user['id'])) {
            return 300;
        }

        if ($this->userBusyOrFree($data['date_from'],$data['date_to'],$user_second)) {
            return 301;
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

    public function cancelMeeting($user,$slot) {
        try {
            $this->_db->beginTransaction();
            $sid = $slot['id'];
            $this->update(array(
                'status' => 4
            ),"id = $sid");

            $text = $user['name'].' '.substr($user['lastname'], 0, 1).'. отменил встречу '.$slot['start_time'].' в '.$slot['place'];
            $this->_db->insert('notifications',array(
                'from' => $user['id'],
                'to' => $slot['user_id_second'],
                'type' => 6,
                'text' => $text
            ));

            $push = new Application_Model_DbTable_Push();
            $push->sendPush($slot['user_id_second'],$text,0,array(
                'from' => $user['id'],
                'to' => $slot['user_id_second'],
                'type' => 1
            ));

            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
        return true;
    }

    public function deleteSlot($user,$id) {
        $user_id = $user['id'];
        $slot = $this->getSlot($id,$user_id,true);
        if ($slot) {
            if ($slot['type'] === true) {
                $this->delete("id = $id");
                return 200;
            }
            elseif ($slot['type'] === '2') {
                $this->cancelMeeting($user,$slot);
                return 200;
            }
        }
        return 404;
    }


    public function addSlots($data,$user) {
        $slots = $data['slots'];
        $user_id = $user['id'];
        $calendars = @json_decode($data['calendars'],true);

        if ($calendars) {
            $filter = new Zend_Filter_Alnum(true);
            foreach ($calendars as $num => $row) {
                $calendars[$num] = $filter->filter($row);
            }
            $calendars = "'".implode("','",$calendars)."'";
            // Fetch busy slots of user specified calendar
            $old_slots = $this->_db->fetchOne("
            select group_concat(`hash`)
            from calendar
            where user_id = $user_id
            and `type` = 0
            and calendar_name in ($calendars)
        ");
            $che_slots = explode(',',$old_slots);
            $old_slots = array();
            foreach ($slots as $num => $row ) {
                if (!in_array($row['hash'],$che_slots) && !in_array($row['hash'],$old_slots)) {
                    $row['user_id'] = $user['id'];
                    $row['start_time'] = date('Y-m-d H:i:s',(int)$row['start_time']);
                    $row['end_time'] = date('Y-m-d H:i:s',(int)$row['end_time']);
                    unset($row['private_key']);
                    $row['type'] = 0;
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
            return true;
        }
        return false;
    }


}