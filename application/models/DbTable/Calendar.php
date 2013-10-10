<?php

class Application_Model_DbTable_Calendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'calendar';

    public function getSlotID($id,$many = false) {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        if (!$many) {
            $id = "c.id = $id";
        }
        else {
            $id = "c.id in ($id)";
        }
        $res = $this->_db->fetchAll("
            select c.id,c.user_id,c.user_id_second,unix_timestamp(c.start_time) as start_time,unix_timestamp(c.end_time) as end_time,c.goal,c.city,c.city_name,c.foursquare_id,c.place,c.lat,c.lng,c.rating,c.type,c.status,c.email,c.offset,
             case
              when c.email = 0 then case
                                      when (select distinct(f.id)
                                            from user_friends f
                                            where f.user_id = c.user_id
                                            and f.friend_id = u.id
                                            and f.status = 1 limit 1) > 0 then concat(u.name,' ',u.lastname)
                                      else concat(u.name,' ',substr(u.lastname,1,1),'.')
                                    end
              else e.name
             end as fullname,
            case
              when c.email = 0 then concat('$url',u.photo)
              else ''
            end as photo
            from calendar c
            left join users u on c.user_id_second = u.id and c.email = 0
            left join email_users e on c.user_id_second = e.id and c.email = 1
            where
            $id
        ");

        if (!$many && isset($res[0])) {
            $res = $res[0];
        }

        return $res;
    }

    public function getSocial($id,$uid) {
        if (!$slot = $this->getSlotID($id)) {
            return 404;
        }

        $db = new Application_Model_DbTable_Users();
        if (!$user = $db->getUser($uid,false,'id',true,false)) {
            return 400;
        }

        return array(
            'user' => $user,
            'slot' => $slot,
            'second_user' => $db->getUser($slot['user_id_second'],false,'id',true,false)
        );
    }

    public function getAll($user) {
        $user_id = $user['id'];
        $start = gmdate('Y-m-d H:i:s',time()-86400);
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        $res = $this->_db->fetchAll("
            select c.id,c.user_id,c.user_id_second,unix_timestamp(c.start_time) as start_time,unix_timestamp(c.end_time) as end_time,c.goal,c.city,c.city_name,c.foursquare_id,c.place,c.lat,c.lng,c.rating,c.type,c.status,c.email,c.offset,
             case
              when c.email = 0 and user_id_second is not null then case
                                                                      when (select distinct(f.id)
                                                                            from user_friends f
                                                                            where f.user_id = $user_id
                                                                            and f.friend_id = u.id
                                                                            and f.status = 1 limit 1) > 0 then concat(u.name,' ',u.lastname)
                                                                      else concat(u.name,' ',substr(u.lastname,1,1),'.')
                                                                    end
              when user_id_second is not null then e.name
              else null
             end as fullname,
            case
              when c.email = 0 and user_id_second is not null then concat('$url',u.photo)
              else ''
            end as photo,
            case
              when c.email = 0 and user_id_second is not null then j.name
              else ''
            end as job,
            case
              when c.email = 0 and user_id_second is not null then j.company
              else ''
            end as company
            from calendar c
            left join users u on c.user_id_second = u.id and c.email = 0
            left join user_jobs j on c.user_id_second = j.user_id and c.email = 0 and j.type = 0 and j.current = 1
            left join email_users e on c.user_id_second = e.id and c.email = 1
            where
            c.user_id = $user_id and (
            (c.status in (2,4) and c.type = 2) or c.end_time > '$start'
            )
            group by c.id
        ");

        return $res;
    }

    public function answerMeetingEmail($user,$slot_id,$status) {
        if ($status == 5) {
            $this->update(array(
                'status' => 3
            ),"id = $slot_id");
            return 200;
        }
        elseif ($status == 4) {
            if (!$slot = $this->getSlot($slot_id,$user['id'],true,true)) {
                return 404;
            }

            if (!Application_Model_DbTable_Users::isValidUser($slot['user_id'])) {
                return 408;
            }

            if ($this->userBusyOrFree(strtotime($slot['start_time']),strtotime($slot['end_time']),$slot['user_id'])) {
                return 301;
            }

            $this->_db->beginTransaction();
            try {
                $this->update(array(
                    'status' => 2
                ),"id = $slot_id");

                $text = '$$$name$$$ accept meeting request $$$date$$$ at '.$slot['place'];
                (new Application_Model_DbTable_Notifications())->insertNotification(array(
                    'from' => $user['id'],
                    'to' => $slot['user_id'],
                    'type' => 4,
                    'item' => $slot['id'],
                    'text' => $text,
                    'template' => 0,
                    'action' => 1
                ));

                $text = 'Meeting Request Accepted';
                (new Application_Model_DbTable_Push())->sendPush($slot['user_id'],$text,2,array(
                    'from' => $user['id'],
                    'type' => 2,
                    'item' => $slot['id'],
                    'action' => 1
                ));

                $this->_db->commit();
            }
            catch (Exception $e) {
                $this->_db->rollBack();
                return 500;
            }

            return 200;
        }

        return 400;
    }

    public function answerMeeting($user,$id,$status,$foursqure_id,$start_time) {
        if (!$slot_id = $this->_db->fetchOne("select `item` from notifications where id = $id and type in (3,9)")) {
            return 400;
        }

        // If meeting reject
        if ($status == 5) {
            $this->_db->beginTransaction();
            try {
                $this->_db->update('notifications',array(
                    'status' => 1
                ),"id = $id");
                $this->update(array(
                    'status' => 3
                ),"id = $slot_id");
                $this->_db->commit();
                return 200;
            }
            catch (Exception $e) {
                $this->_db->rollBack();
                return 500;
            }
        }

        // If meeting accept
        elseif ($status == 4) {
            if (!$slot = $this->getSlot($slot_id,$user['id'],true,true)) {
                return 404;
            }

            if ($start_time) {
                $start_time = gmdate('Y-m-d H:i:s',(int)$start_time);
                $new_slot['start_time'] = $start_time;
            }
            else {
                $new_slot['start_time'] = $slot['start_time'];
            }
            $new_slot['end_time'] = strtotime($new_slot['start_time']) + 3600;
            $new_slot['end_time'] = gmdate('Y-m-d H:i:s',(int)$new_slot['end_time']);

            if ($bid = $this->userBusyOrFree(strtotime($new_slot['start_time']),strtotime($new_slot['end_time']),$user['id'],true)) {
                $bid = implode(',',$bid);
                return array(
                    'body' => $this->getSlotID($bid,true),
                    'error' => 300
                );
            }

            if (!Application_Model_DbTable_Users::isValidUser($slot['user_id'])) {
                return 408;
            }

            if ($this->userBusyOrFree(strtotime($new_slot['start_time']),strtotime($new_slot['end_time']),$slot['user_id'])) {
                return 301;
            }

            $this->_db->beginTransaction();
            try {

                if ($foursqure_id) {
                    $new_slot = array_merge($new_slot,Application_Model_Common::getPlace($foursqure_id));
                    $slot = array_merge($slot,$new_slot);
                }
                elseif (!$slot['foursquare_id'] || empty($slot['foursquare_id'])) {
                    $this->_db->rollBack();
                    return 400;
                }

                $new_slot['status'] = 2;
                $this->update($new_slot,"id = $slot_id");

                $this->expireMeeting($user['id'],$slot);

                $this->_db->update('notifications',array(
                    'status' => 1
                ),"id = $id");

                $text = '$$$name$$$ accept meeting request $$$date$$$ at '.$slot['place'];
                (new Application_Model_DbTable_Notifications())->insertNotification(array(
                    'from' => $user['id'],
                    'to' => $slot['user_id'],
                    'type' => 4,
                    'item' => $slot['id'],
                    'text' => $text,
                    'template' => 0,
                    'action' => 1
                ));

                $text = 'New Meeting Request';
                (new Application_Model_DbTable_Push())->sendPush($slot['user_id'],$text,2,array(
                    'from' => $user['id'],
                    'type' => 2,
                    'item' => $slot['id'],
                    'action' => 1
                ));

                unset($slot['id']);
                $slot['user_id_second'] = $slot['user_id'];
                $slot['user_id'] = $user['id'];
                $slot['status'] = 2;
                $slot['type'] = 2;
                $slot['start_time'] = $new_slot['start_time'];
                $slot['end_time'] = $new_slot['end_time'];
                $this->insert($slot);

                $this->_db->commit();
            }
            catch (Exception $e) {
                $this->_db->rollBack();
                return 500;
            }

            return 200;
        }
        return 400;
    }

    public function expireMeeting($ids,$slot) {
        $q_in = $slot['start_time'];
        $q_out = $slot['end_time'];
        $items = $this->_db->fetchOne("
            select group_concat(c.id)
            from calendar c
            where
            (
            	(c.start_time between '$q_in' and '$q_out') or
            	(c.end_time between '$q_in' and '$q_out') or
            	(c.start_time < '$q_in' and c.end_time > '$q_out')
            )
              and c.start_time != '$q_out'
              and c.end_time != '$q_in'
              and c.user_id_second in ($ids)
              and c.type = 2
              and c.status = 1
        ");
        if ($items) {
            $this->_db->update('notifications',array(
                'status' => 2
            ),"`item` in ($items)");

            $this->update(array(
                'status' => 5
            ),"id in ($items)");
        }
        return true;
    }

    public function getSlot($sid, $user_id, $meet_notStart = false, $second_user = false) {
        if (!$second_user) {
            $res = $this->fetchRow("id = $sid and user_id = $user_id");
        }
        else {
            $res = $this->fetchRow("id = $sid and user_id_second = $user_id");
        }
        if ($res) {
            $res = $res->toArray();
            if ($res['type'] == 1 && strtotime($res['end_time']) > time()) {
                return $res;
            }
            elseif (!$meet_notStart && $res['type'] == 2 && $res['status'] == 2 && strtotime($res['end_time']) < time()) {
                return $res;
            }
            elseif ($meet_notStart && $res['type'] == 2 && $res['status'] == 1 && strtotime($res['end_time']) > time()+3600) {
                return $res;
            }
            elseif ($meet_notStart && $res['type'] == 2 && $res['status'] == 2 && strtotime($res['start_time']) > time()-3600) {
                return $res;
            }
        }
        return false;
    }

    public function prepeareSlotCreate($user,$data,$type=0,$status=0,$user_second = false, $email = false) {
        $res['start_time'] = gmdate('Y-m-d H:i:s',(int)$data['date_from']);
        $res['end_time'] = gmdate('Y-m-d H:i:s',(int)$data['date_to']);
        $res['user_id'] = $user['id'];
        $res['type'] = $type;
        $res['status'] = $status;
        $res = array_merge($res,Application_Model_Common::getCity($data['city']));

        if (isset($data['foursquare_id']) && $data['foursquare_id']) {
            $place = Application_Model_Common::getPlace($data['foursquare_id']);
            $res = array_merge($res,$place);
        }
        elseif ($email) {
            return false;
        }

        if (isset($data['goal']) && is_numeric($data['goal'])) {
            $res['goal'] = $data['goal'];
        }

        if (isset($data['offset']) && is_numeric($data['offset'])) {
            $res['offset'] = $data['offset'];
        }
        else {
            $res['offset'] = 0;
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

        $data['date_to'] = (int)$data['date_from'] + 3600;

        if ($bid = $this->userBusyOrFree($data['date_from'],$data['date_to'],$user['id'],true)) {
            $bid = implode(',',$bid);
            return array(
                'body' => $this->getSlotID($bid,true),
                'error' => 300
            );
        }

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
            if (!$data) {
                $this->_db->rollBack();
                return 400;
            }
            $data['hash'] = uniqid(sha1(time()), false);
            $id = $this->insert($data);

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->email->url;

            if (isset($data['place'])) {
                $place = ' в '.$data['place'];
            }
            else {
                $place = '';
            }
            $time = strtotime($data['start_time'])+$data['offset'];
            $time = gmdate('Y-m-d H:i:s',(int)$time);
            $options = array(
                'name' => $user['name'],
                'lastname' => substr($user['lastname'], 0, 1),
                'time' => $time,
                'place' => $place,
                'url_ok' => $url.'meetings/?answer=4&sid='.$id.'&key='.$key,
                'url_nok' => $url.'meetings/?answer=5&sid='.$id.'&key='.$key
            );
            Application_Model_Common::sendEmail($email, "Реквест Митинг!", null, null, null, "meeting_request.phtml", $options, 'meeting_request');


            $this->_db->commit();
            return $this->getSlotID($id);
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
    }

    public function createFreeSlot($user,$data) {
        $this->_db->beginTransaction();
        try {
            $data = $this->prepeareSlotCreate($user,$data,1);
            if ($this->haveFreeSlot($data['start_time'],$data['end_time'],$user['id'])){
                return 300;
            }
            $id = $this->insert($data);
            $this->_db->commit();
            return $this->getSlotID($id);
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
            (
            	(c.start_time between '$q_in' and '$q_out') or
            	(c.end_time between '$q_in' and '$q_out') or
            	(c.start_time < '$q_in' and c.end_time > '$q_out')
            )
              and c.start_time != '$q_out'
              and c.end_time != '$q_in'
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

    public function userBusyOrFree($q_in,$q_out,$user_id,$return_id = false) {
        $q_in = gmdate('Y-m-d H:i:s',(int)$q_in);
        $q_out = gmdate('Y-m-d H:i:s',(int)$q_out);
        $che = $this->_db->fetchOne("
            select group_concat(c.id)
            from calendar c
            where
            (
            	(c.start_time between '$q_in' and '$q_out') or
            	(c.end_time between '$q_in' and '$q_out') or
            	(c.start_time < '$q_in' and c.end_time > '$q_out')
            )
              and c.start_time != '$q_out'
              and c.end_time != '$q_in'
              and c.user_id = $user_id
              and c.type = 2
              and c.status = 2
        ");
        if ($che) {
            $che = explode(',',$che);
        }
        if (isset($che[0]) && is_numeric($che[0])) {
            if ($return_id) {
                return $che;
            }
            return true;
        }
        else {
            return false;
        }
    }

    public function meetWithYouThisTime($q_in,$q_out,$user_id,$user_second) {
        $q_in = gmdate('Y-m-d H:i:s',(int)$q_in);
        $q_out = gmdate('Y-m-d H:i:s',(int)$q_out);
        $che = $this->_db->fetchOne("
            select c.id
            from calendar c
            where
            (
            	(c.start_time between '$q_in' and '$q_out') or
            	(c.end_time between '$q_in' and '$q_out') or
            	(c.start_time < '$q_in' and c.end_time > '$q_out')
            )
              and c.start_time != '$q_out'
              and c.end_time != '$q_in'
              and c.user_id = $user_id
              and c.user_id_second = $user_second
              and c.type = 2
              and c.status = 1
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


        if (isset($data['person_value']) && is_numeric($data['person_value']) && Application_Model_DbTable_Users::isValidUser($data['person_value'])) {
            $user_second = $data['person_value'];
        }
        else {
            return 408;
        }

        if ($bid = $this->userBusyOrFree($data['date_from'],$data['date_to'],$user['id'],true)) {
            $bid = implode(',',$bid);
            return array(
                'body' => $this->getSlotID($bid,true),
                'error' => 300
            );
        }

        if ($this->meetWithYouThisTime($data['date_from'],$data['date_to'],$user['id'],$user_second)) {
            return 301;
        }

        $user_second_free = true;
        if ($this->userBusyOrFree($data['date_from'],$data['date_to'],$user_second)) {
            $user_second_free = false;
        }

        try {
            $this->_db->beginTransaction();
            $data = $this->prepeareSlotCreate($user,$data,2,1,$user_second);
            $data['hash'] = uniqid(sha1(time()), false);
            $id = $this->insert($data);

            $text = 'New meeting request';
            if ($user_second_free) {
                $nType = 3;
                $pType = 0;
            }
            else {
                $nType = 9;
                $pType = 6;
            }


            $idn = (new Application_Model_DbTable_Notifications())->insertNotification(array(
                'from' => $user['id'],
                'to' => $user_second,
                'type' => $nType,
                'item' => $id,
                'text' => $text,
                'template' => 2,
                'action' => 0
            ));

            (new Application_Model_DbTable_Push())->sendPush($user_second,$text,0,array(
                'from' => $user['id'],
                'type' => $pType,
                'item' => $idn,
                'action' => 0
            ));

            $this->_db->commit();
            return $this->getSlotID($id);
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
                $res['start_time'] = gmdate('Y-m-d H:i:s',(int)$data['date_from']);
            }

            if (isset($data['date_to'])) {
                $res['end_time'] = gmdate('Y-m-d H:i:s',(int)$data['date_to']);
            }

            if (isset($data['goal']) && is_numeric($data['goal'])) {
                $res['goal'] = $data['goal'];
            }

            if (isset($data['city']) && $data['city']) {
                $place = Application_Model_Common::getCity($data['city']);
                $res = array_merge($res,$place);
            }

            if (isset($data['offset']) && $data['offset']) {
                $res['offset'] = $data['offset'];
            }

            if (isset($data['foursquare_id']) && $data['foursquare_id']) {
                $place = Application_Model_Common::getPlace($data['foursquare_id']);
                $res = array_merge($res,$place);
            }

        }

        return $res;
    }

    public function updateRating($data,$slot) {
        $sid = $slot['id'];
        $this->update(array(
            'rating' => $data['rating']
        ),"id = $sid");

        if ((int)$data['rating'] < 3) {
            $user_id = $slot['user_id_second'];
            $this->_db->update('users',array(
                'meet_declined' => '`meet_declined`+1',
                'meet_succesfull' => '`meet_succesfull`-1'
            ),"id = $user_id");
        }

        return $this->getSlotID($sid);
    }

    public function updateSlot($data,$slot,$user) {
        if ($slot['type'] == 1 && isset($data['person']) && is_numeric($data['person'])) {
            $res = 400;
            $sid = $slot['id'];
            if ($data['person'] == 0) {
                $data = $this->prepeareUpdate($data);
                if ($data) {
                    $this->update($data,"id = $sid");
                }
                return $this->getSlotID($sid);
            }
            elseif ($data['person'] == 1) {
                $data = $this->prepeareUpdate($data,$slot);
                $res = $this->createMeeting($user,$data);
            }
            elseif ($data['person'] == 2) {
                $data = $this->prepeareUpdate($data,$slot);
                $res = $this->createMeetingEmail($user,$data);
            }

            if (isset($res['id'])) {
                $this->delete("id = $sid");
            }

            return $res;
        }

        return 400;
    }

    public function cancelMeeting($user,$slot) {
        try {
            $this->_db->beginTransaction();
            $hash = $slot['hash'];
            $uid = $user['id'];
            $slot2 = $this->fetchRow("`hash` = '$hash' and user_id_second = $uid");

            $this->update(array(
                'status' => 4
            ),"hash = '$hash'");

            $sid1 = $slot['id'];
            $sid2 = $slot2['id'];
            $this->_db->update('notifications',array(
                'status' => 2
            ),"(item = $sid1 or item = $sid2) and type = 4");


            if ($slot['email'] == 1) {
                if ( $user_email = Application_Model_DbTable_EmailUsers::getUserId($slot2['user_id']) ) {
                    $options = array(
                        'name' => $user_email['name'],
                    );
                    Application_Model_Common::sendEmail($user_email['email'], "Митинг отменен!", null, null, null, "meeting_canceled.phtml", $options, 'meeting_canceled');
                }
            }
            else {
                $text = '$$$name$$$ canceled meeting $$$date$$$ at '.$slot['place'];
                (new Application_Model_DbTable_Notifications())->insertNotification(array(
                    'from' => $user['id'],
                    'to' => $slot2['user_id'],
                    'item' => $slot2['id'],
                    'type' => 6,
                    'text' => $text,
                    'template' => 1,
                    'action' => 1
                ));

                $text = 'Meeting canceled'.$slot['place'];
                (new Application_Model_DbTable_Push())->sendPush($slot['user_id_second'],$text,1,array(
                    'from' => $user['id'],
                    'type' => 1,
                    'item' => $slot2['id'],
                    'action' => 1
                ));
            }

            $this->_db->commit();
            return 200;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return 500;
        }
        return true;
    }

    public function deleteSlotReal($user,$id,$user_id) {
        $slot = $this->getSlot($id,$user_id,true);
        if ($slot) {
            if ($slot['type'] === '1') {
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

    public function deleteSlot($user,$id) {
        $user_id = $user['id'];

        if (is_numeric($id)) {
            return $this->deleteSlotReal($user,$id,$user_id);
        }
        else {
            $id = explode(',',$id);
            if (isset($id[0])) {
                foreach ($id as $row) {
                    if (is_numeric($row)) {
                        $this->deleteSlotReal($user,$row,$user_id);
                    }
                }
            }
            return 200;
        }
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
            if ($calendars) {
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
                    $validators = array(
                        '*' => array('NotEmpty')
                    );
                    $filters = array(
                        'calendar_name' => array('StringTrim','HtmlEntities',$filter),
                        'hash' => array('StringTrim','HtmlEntities',$filter),
                        'start_time' => array('StringTrim','HtmlEntities','Int'),
                        'end_time' => array('StringTrim','HtmlEntities','Int'),
                    );
                    $input = new Zend_Filter_Input($filters, $validators, $row);

                    if ($input->getEscaped('hash') && !in_array($input->getEscaped('hash'),$che_slots) && !in_array($input->getEscaped('hash'),$old_slots)) {
                        $row = array();
                        $row['user_id'] = $user['id'];
                        $row['hash'] = $input->getEscaped('hash');
                        $row['calendar_name'] = $input->getEscaped('calendar_name');
                        $row['start_time'] = gmdate('Y-m-d H:i:s',(int)$input->getEscaped('start_time'));
                        $row['end_time'] = gmdate('Y-m-d H:i:s',(int)$input->getEscaped('end_time'));
                        $row['type'] = 0;
                        $this->insert($row);
                    }

                    if ($input->getEscaped('hash') && !in_array($row['hash'],$old_slots)) {
                        array_push($old_slots,$input->getEscaped('hash'));
                    }
                }

                $old_slots = "'".implode("','",$old_slots)."'";
                if ($old_slots) {
                    $this->delete("user_id = $user_id and `hash` not in ($old_slots) and type = 0");
                }
                return true;
            }
        }
        return false;
    }


}