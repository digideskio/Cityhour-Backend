<?php

class Application_Model_DbTable_EmailUsers extends Zend_Db_Table_Abstract
{

    protected $_name = 'email_users';

    public function addUserEmail($data) {
        return $this->insert($data);
    }

    public static function getUser($key) {
        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select *
            from email_users
            where `key` = '$key' and `status` = 0
        ");
        if ($res && $res != null) {
            return $res;
        }
        else {
            return false;
        }
    }

}

