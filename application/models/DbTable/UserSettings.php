<?php

class Application_Model_DbTable_UserSettings extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_settings';

    public function updateSettings($user,$data) {
        $user_id = $user['id'];
        
        $validator_exist = $this->_db->fetchCol("
            select `name`
            from user_settings
            where user_id = $user_id
        ");

        $filter = new Zend_Filter_HtmlEntities();
        foreach ($data as $num => $row ) {
            $num = trim($filter->filter($num));
            $row = trim($filter->filter($row));

            if ($num == 'city') {
                $city = Application_Model_Common::getCity($row);
                $ucity = array(
                    'free_city' => $city['city'],
                    'free_city_name' => $city['city_name'],
                    'free_lat' => $city['lat'],
                    'free_lng' => $city['lng'],
                );
                if (array_key_exists('foursquare_id', $data) || $this->fetchRow("name = 'foursquare_id' and user_id = $user_id")) {
                    unset($ucity['lat']);
                    unset($ucity['lng']);
                }

                $this->_db->update('users',$ucity,"id = $user_id");
            }

            if ($num == 'foursquare_id') {
                if ($row) {
                    $foursquare = Application_Model_Common::getPlace($row);
                    $ufoursquare = array(
                        'free_foursquare_id' => $foursquare['foursquare_id'],
                        'free_place' => $foursquare['place'],
                        'free_lat' => $foursquare['lat'],
                        'free_lng' => $foursquare['lng'],
                    );
                }
                else {
                    $city = $this->_db->fetchRow("
                        select c.lat,c.lng
                        from user_settings us
                        left join city c on us.user_id = $user_id and us.name = 'city' and us.value = c.city
                    ");
                    $ufoursquare = array(
                        'free_foursquare_id' => null,
                        'free_place' => null,
                        'free_lat' => $city['lat'],
                        'free_lng' => $city['lng'],
                    );
                    $row = '';
                }
                $this->_db->update('users',$ufoursquare,"id = $user_id");
            }

            if ($num == 'offset') {
                $row = (int)$row;
            }

            if (!in_array($num,$validator_exist)) {
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

        // Update User free time
        Application_Model_Common::updateUserFreeSlots($user_id);
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

                if ($row['name'] == 'foursquare_id' && $row['value']) {
                    $foursquare_id = $row['value'];
                    $res[$num]['value'] = $this->_db->fetchOne("select place from place where foursquare_id = '$foursquare_id' ");
                }
                elseif ($row['name'] == 'foursquare_id') {
                    $res[$num]['value'] = '';
                }
            }

            return $res;
        }
        else {
            return array();
        }
    }

}