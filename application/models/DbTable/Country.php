<?php

class Application_Model_DbTable_Country extends Zend_Db_Table_Abstract
{

    protected $_name = 'country';


    public function getAll() {
        return $this->fetchAll()->toArray();
    }

    public function getName($code) {
        $res = $this->fetchRow("code = '$code'");
        if ($res) {
            $res = $res->toArray()['name'];
        }
        else {
            $res = null;
        }
        return $res;
    }

}

