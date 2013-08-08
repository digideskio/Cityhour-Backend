<?php

class Application_Model_DbTable_Push extends Zend_Db_Table_Abstract
{

    protected $_name = 'push';

    public function addToken($id, $token, $device, $debug) {

        $puID = $this->fetchRow("user_id = $id and device = '$device'")['id'];
        if (!is_numeric($puID)) {
            $this->insert(array(
                'user_id' => $id,
                'deviceToken' => $token,
                'device' => $device,
                'debug' => $debug
            ));
        }
        else {
            $this->update(array(
                'deviceToken' => $token,
                'debug' => $debug
            ),"id = $puID");
        }
        return true;
    }

    public function deletePush($id, $device) {
        $this->delete("user_id = $id and device = $device");
        return true;
    }

    public function sendPush($user_id,$data,$type,$alert) {
        $data = json_encode($data);
        $this->_db->insert('push_messages',array(
            'user_id' => $user_id,
            'type' => $type,
            'data' => $data,
            'alert' => $alert
        ));
        return true;
    }


}

