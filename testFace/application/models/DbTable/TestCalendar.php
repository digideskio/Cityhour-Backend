<?php

class Application_Model_DbTable_TestCalendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_calendar';

    public function getAll($id) {
        return $this->_db->fetchAll("
            select c.*, u.name as user
            from test_calendar c
            left join test_users u on c.user_id_second = u.id
            where
            user_id = $id
        ");
    }

    public function getEvent($id) {
        $res = $this->fetchRow("id = $id")->toArray();
        return $res;
    }

    public function saveEvent($data,$uid,$id) {
        $city = (new Application_Model_DbTable_TestCity())->getCity($data['city']);
        unset($data['ok']);
        $data['user_id'] = $uid;
        $data['city'] = $city['city'];
        $data['city_name'] = $city['city_name'];
        $data['lat'] = $city['lat'];
        $data['lng'] = $city['lng'];
        $this->update($data,"id = $id");
        return true;
    }

    public function addEvent($data,$uid) {
        unset($data['ok']);
        $city = (new Application_Model_DbTable_TestCity())->getCity($data['city']);
        $data['user_id'] = $uid;
        $data['city'] = $city['city'];
        $data['lat'] = $city['lat'];
        $data['lng'] = $city['lng'];
        $data['city_name'] = $city['city_name'];
        return $this->insert($data);
    }

    public function deleteEvent($id){
        return $this->delete("id = $id");
    }


    public static function typeCalendar() {
        return array(
            0 => 'Busy time',
            1 => 'Free time',
            2 => 'Meeting'
        );
    }

    public static function statusCalendar() {
        return array(
            0 => 'Default',
            1 => 'Meeting Request',
            2 => 'Meeting Accepted',
            3 => 'Meeting Rejected',
            4 => 'Meeting Canceled',
            5 => 'Meeting Expired'
        );
    }
}

