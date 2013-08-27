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

    public function prepeareSlotCreate($user,$data,$type=0,$status=0,$user_second = false, $email = false) {
        $res['start_time'] = date('Y-m-d H:i:s',(int)$data['date_from']);
        $res['end_time'] = date('Y-m-d H:i:s',(int)$data['date_to']);
        $res['user_id'] = $user['id'];
        $res['type'] = $type;
        $res['status'] = $status;
        $res = array_merge($res,Application_Model_Common::getCity($data['city']));

        if (isset($data['foursquare_id']) && $data['foursquare_id']) {
            $place = Application_Model_Common::getPlace($data['foursquare_id']);
            unset($place['lat']);
            unset($place['lng']);
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
            $data = $this->prepeareSlotCreate($user,$data,2,1);
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

    public function updateSlot($user,$data,$id) {
        $user_id = $user['id'];
        $this->update($data,"id = $id and user_id = $user_id");
        return true;
    }

    public function deleteSlot($user,$id) {
        $user_id = $user['id'];
        $this->delete("id = $id and user_id = $user_id");
        return true;
    }
}

