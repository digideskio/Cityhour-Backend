<?php

class Application_Model_DbTable_Industries extends Zend_Db_Table_Abstract
{

    protected $_name = 'industries';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }

    public function getID($name) {
        $res = $this->fetchRow("name = '$name'");
        if ($res) {
            $res = $res->toArray()['id'];
        }
        else {
            $res = null;
        }
        return $res;
    }

}

