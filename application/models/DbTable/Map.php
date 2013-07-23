<?php

class Application_Model_DbTable_Map extends Zend_Db_Table_Abstract
{

    protected $_name = 'map';

    public function updateMap($user,$lat,$lng) {
        $user_id = $user['id'];
        $che = $this->fetchRow("user_id = $user_id");
        $data = array(
            'user_id' => $user_id,
            'lat' => $lat,
            'lng' => $lng,
            'status' => 1,
            'time' => date('Y-m-d H:i:s',time())
        );
        if ($che != null) {
            $id = $che['id'];
            $this->update($data,"id = $id");
        }
        else {
            $this->insert($data);
        }
        return true;
    }

    public function getNear($user)
    {
        $user_id = $user['id'];
        $map = $this->fetchRow("user_id = $user_id and status = 1");
        if ($map != null) {
            $lng = $map['lng'];
            $lat = $map['lat'];
            $res = $this->_db->fetchAll("
                    SELECT m.user_id, m.lat, m.lng, m.time, u.name, u.lastname, u.photo
                    FROM map m
                    left join users u on m.user_id = u.id
                    WHERE
                    user_id != $user_id
                    AND lng BETWEEN $lng-0.1 AND $lng+0.1 AND lat BETWEEN $lat-0.1 AND $lat+0.1
                ");
        } else {
            $res = false;
        }
        return $res;
    }

}

