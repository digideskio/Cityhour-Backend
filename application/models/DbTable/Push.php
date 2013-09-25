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
        $this->delete("user_id = $id and device = '$device'");
        return true;
    }

    public function checkSettings($user_id,$type) {
        $settings = array(
            0 => 'incomingMeetingInviteSync',
            1 => 'incomingMeetingInviteSync',
            2 => 'incomingMeetingInviteSync',
            3 => 'newMessageSync',
            4 => 'contactRequestSync',
        );
        $settings = $settings[$type];
        $che = (int)$this->_db->fetchOne("
            select `value`
            from user_settings
            where user_id = $user_id and `name` = '$settings'
        ");
        if ($che === 1) {
            return true;
        }
        else {
            return false;
        }
    }

    public function sendPush($user_id,$alert,$type,$data) {
        $data = json_encode($data);
        $alert = mb_substr($alert,0,15,'UTF-8');
        if ($this->checkSettings($user_id,$type)) {
            $this->_db->insert('push_messages',array(
                'user_id' => $user_id,
                'type' => $type,
                'data' => $data,
                'alert' => $alert
            ));
        }
        return true;
    }


}

