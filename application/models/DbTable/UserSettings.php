<?php

class Application_Model_DbTable_UserSettings extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_settings';

    public function updateSettings($user,$data) {
        $user_id = $user['id'];
        $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'user_settings',
            'field' => 'name',
            'exclude' => "user_id = $user_id"
        ));

        $filter = new Zend_Filter_HtmlEntities();
        foreach ($data as $num => $row ) {
            $num = trim($filter->filter($num));
            $row = trim($filter->filter($row));

            if ($num == 'city') {
                $city = Application_Model_Common::getCity($row);
                $ucity = array(
                    'free_city' => $city['city'],
                    'free_city_name' => $city['city_name'],
                    'lat' => $city['free_lat'],
                    'lng' => $city['free_lng'],
                );
                if (array_key_exists('foursquare_id', $data)) {
                    unset($ucity['lat']);
                    unset($ucity['lng']);
                }

                $this->_db->update('users',$ucity,"id = $user_id");
            }

            if ($num == 'foursquare_id') {
                $foursquare = Application_Model_Common::getPlace($row);
                $ufoursquare = array(
                    'free_foursquare_id' => $foursquare['foursquare_id'],
                    'place' => $foursquare['place'],
                    'lat' => $foursquare['free_lat'],
                    'lng' => $foursquare['free_lng'],
                );
                $this->_db->update('users',$ufoursquare,"id = $user_id");
            }

            if ($num == 'offset') {
                $row = (int)$row;
            }

            if ($validator_exist->isValid($num)) {
                $row = array(
                    'user_id' => $user_id,
                    'name' => $num,
                    'value' => $row
                );
                $this->insert($row);
            }
            else {
                $row = array(
                    'name' => $num,
                    'value' => $row
                );
                $this->update($row,"user_id = $user_id and name = '$num' ");
            }
        }
        return true;
    }

    public function getSettings($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("user_id = $user_id");
        if ($res) {
            $res = $res->toArray();

            foreach ($res as $num => $row) {
                if ($row['name'] == 'city') {
                    $city = $row['value'];
                    $res[$num]['value'] = $this->_db->fetchOne("select city_name from city where city = '$city' ");
                }

                if ($row['name'] == 'foursquare_id') {
                    $foursquare_id = $row['value'];
                    $res[$num]['value'] = $this->_db->fetchOne("select place from place where foursquare_id = '$foursquare_id' ");
                }
            }

            return $res;
        }
        else {
            return array();
        }
    }

}