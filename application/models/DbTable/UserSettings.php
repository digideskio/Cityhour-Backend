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
        foreach ($data as $num => $row ) {
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
                $this->update($row,"user_id = $user_id");
            }
        }
        return true;
    }

    public function getSettings($user) {
        $user_id = $user['id'];
        $res = $this->fetchRow("user_id = $user_id");
        if ($res) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

}

