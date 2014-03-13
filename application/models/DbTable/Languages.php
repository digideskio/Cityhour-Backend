<?php

class Application_Model_DbTable_Languages extends Zend_Db_Table_Abstract
{

    protected $_name = 'languages';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }

    public function getID($name) {
        $id = $this->fetchRow("name = '$name' ");
        if ($id) {
            return $id->toArray()['id'];
        }
        else {
            return false;
        }
    }

}

