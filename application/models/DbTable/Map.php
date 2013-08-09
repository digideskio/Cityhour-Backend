<?php

class Application_Model_DbTable_Map extends Zend_Db_Table_Abstract
{

    protected $_name = 'map';

    public function updateMap($user_id,$lat,$lng) {
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

    public function getNear($user,$lat,$lng)
    {
        $user_id = $user['id'];
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;

        $this->updateMap($user_id,$lat,$lng);
        $res = $this->_db->fetchAll("
               select user_id, lat, lng, `name`, lastname, concat('$url',photo) as photo, job_name, company
               from
               ((SELECT m.user_id, m.lat, m.lng, u.name, u.lastname, u.photo, j.name as job_name, j.company
                FROM map m
                left join users u on m.user_id = u.id
                left join user_jobs j on m.user_id = j.user_id
                WHERE
                m.time > now() - interval 10 MINUTE
                and m.lng BETWEEN $lng-0.1 AND $lng+0.1 AND m.lat BETWEEN $lat-0.1 AND $lat+0.1
                and m.user_id != $user_id
                and j.current=1)
                union
                (SELECT m.user_id, m.lat, m.lng, u.name, u.lastname, u.photo, j.name as job_name, j.company
                FROM calendar m
                left join users u on m.user_id = u.id
                left join user_jobs j on u.id = j.user_id
                WHERE
                m.start_time between now() and now() + interval 2 HOUR
                and m.type = 1
                and m.status = 0
                and m.lng BETWEEN $lng-0.1 AND $lng+0.1 AND m.lat BETWEEN $lat-0.1 AND $lat+0.1
                and m.user_id != $user_id
                and j.current=1))
                as tt
                group by user_id
            ");
        return $res;
    }

}

