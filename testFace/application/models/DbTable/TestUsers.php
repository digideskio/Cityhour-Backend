<?php

class Application_Model_DbTable_TestUsers extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_users';

    public function insertTestData() {
        $users = $this->fetchAll()->toArray();
        foreach ($users as $row) {
            $id = $row['id'];

            $this->_db->delete("users","id = $id");
            $this->_db->delete("user_jobs","user_id = $id");
            $this->_db->delete("user_settings","user_id = $id");
            $this->_db->delete("calendar","user_id = $id");

            $row['private_key'] = $row['id'];
            unset($row['order']);


            $this->_db->insert('users',$row);
            $jobs = (new Application_Model_DbTable_TestUserJobs())->fetchAll("user_id = $id")->toArray();
            $settings = (new Application_Model_DbTable_TestUserSettings())->fetchAll("user_id = $id")->toArray();
            $calendar = (new Application_Model_DbTable_TestCalendar())->fetchAll("user_id = $id")->toArray();

            foreach ($jobs as $rrow) {
                unset($rrow['id']);
                $this->_db->insert("user_jobs",$rrow);
            }

            foreach ($settings as $rrow) {
                unset($rrow['id']);
                $this->_db->insert("user_settings",$rrow);
            }

            foreach ($calendar as $rrow) {
                unset($rrow['id']);
                $day = date("Y-m-d ",time()+172800);
                $rrow['start_time'] = $day.$rrow['start_time'];
                $rrow['end_time'] = $day.$rrow['end_time'];
                $this->_db->insert("calendar",$rrow);
            }
        }
    }

    public function deleteUser($id) {
        $this->delete("id = $id");
        $this->_db->delete("test_user_settings","user_id = $id");
        $this->_db->delete("test_user_jobs","user_id = $id");
        return true;
    }

    public function getList() {
        return $this->_db->fetchPairs($this->_db->select()->from($this->_name, array('id', 'name')));
    }

    public function getAll() {
        return $this->_db->fetchAll("
            select u.*, i.name as industry, g.name as goal, f.value as free_time, o.value as offset
            from test_users u
            left join industries i on u.industry_id = i.id
            left join test_user_settings s on u.id = s.user_id and s.name = 'goal'
            left join test_user_settings f on u.id = f.user_id and f.name = 'free_time'
            left join test_user_settings o on u.id = o.user_id and o.name = 'offset'
            left join goals g on s.value = g.id
        ");
    }

    public function getUser($id) {
        return $this->_db->fetchRow("
            select u.*, i.name as industry, g.name as goal, f.value as is_free, o.value as offset
            from test_users u
            left join industries i on u.industry_id = i.id
            left join test_user_settings s on u.id = s.user_id and s.name = 'goal'
            left join test_user_settings f on u.id = f.user_id and f.name = 'free_time'
            left join test_user_settings o on u.id = o.user_id and o.name = 'offset'
            left join goals g on s.value = g.id
            where
            u.id = $id
        ");
    }

    public function saveUser($data,$id) {
        $city = (new Application_Model_DbTable_TestCity())->getCity($data['city']);

        $preData = array(
            'email' => $data['name'].'@gmail.com',
            'name' => $data['name'],
            'industry_id' => $data['industry'],
            'business_email' => $data['name'].'@alterplay.com',
            'city' => $city['city'],
            'city_name' => $city['city_name'],
            'free_city' => $city['city'],
            'free_city_name' => $city['city_name'],
            'free_lat' => $city['lat'],
            'free_lng' => $city['lng'],
        );

        $this->_db->delete("test_user_settings","user_id = $id");

        $this->_db->insert('test_user_settings',array(
            'user_id' => $id,
            'name' => 'city',
            'value' => $city['city'],
        ));

        $this->_db->insert('test_user_settings',array(
            'user_id' => $id,
            'name' => 'free_time',
            'value' => $data['is_free'],
        ));

        $this->_db->insert('test_user_settings',array(
            'user_id' => $id,
            'name' => 'offset',
            'value' => $data['offset'],
        ));

        $this->_db->insert('test_user_settings',array(
            'user_id' => $id,
            'name' => 'goal',
            'value' => $data['goal'],
        ));

        return $this->update($preData,"id = $id");
    }

    public function addUser($data) {
        try {
            $this->_db->beginTransaction();
            $city = (new Application_Model_DbTable_TestCity())->getCity($data['city']);

            $preData = array(
                'email' => $data['name'].'@gmail.com',
                'name' => $data['name'],
                'lastname' => 'Test',
                'industry_id' => $data['industry'],
                'summary' => 'Test user',
                'photo' => '1.png',
                'phone' => '+1234567890',
                'business_email' => $data['name'].'@alterplay.com',
                'city' => $city['city'],
                'city_name' => $city['city_name'],
                'free_city' => $city['city'],
                'free_city_name' => $city['city_name'],
                'free_lat' => $city['lat'],
                'free_lng' => $city['lng'],
                'skype' => 'dumbldore',
                'rating' => 0,
                'experience' => 3,
                'completeness' => 100,
                'contacts' => 5,
                'status' => 0
            );

            $id = $this->insert($preData);


            $this->_db->insert("test_result",array(
                'user_id' => $id,
                'is_free' => 0
            ));

            $this->_db->insert('test_user_settings',array(
                'user_id' => $id,
                'name' => 'city',
                'value' => $city['city'],
            ));

            $this->_db->insert('test_user_settings',array(
                'user_id' => $id,
                'name' => 'free_time',
                'value' => $data['is_free'],
            ));

            $this->_db->insert('test_user_settings',array(
                'user_id' => $id,
                'name' => 'offset',
                'value' => $data['offset'],
            ));

            $this->_db->insert('test_user_settings',array(
                'user_id' => $id,
                'name' => 'goal',
                'value' => $data['goal'],
            ));


            $this->_db->insert('test_user_jobs',array(
                'user_id' => $id,
                'name' => 'Back-end Developer',
                'company' => 'Alterplay',
                'current' => 1,
                'active' => 1,
                'start_time' => '2012-11-01',
                'end_time' => '2012-11-01',
                'type' => 0,
            ));
            $this->_db->commit();
            return $id;
        }
        catch (Exception $e) {
            $this->_db->rollBack();
            return true;
        }
    }
}

