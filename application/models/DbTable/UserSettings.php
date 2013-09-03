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
            $row = $this->_db->quote($row);
            $num = $this->_db->quote($num);

            if ($num == 'city') {
                Application_Model_Common::getCity($row);
            }

            if ($num == 'foursquare_id') {
                Application_Model_Common::getPlace($row);
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