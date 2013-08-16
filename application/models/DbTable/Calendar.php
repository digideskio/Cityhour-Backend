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
                    if (isset($row['city'])) {
                        $row = array_merge($row,Application_Model_Common::getCity($row['city']));
                    }
                    if (isset($row['foursquare_id'])) {
                        $row = array_merge($row,Application_Model_Common::getPlace($row['foursquare_id']));
                    }
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

