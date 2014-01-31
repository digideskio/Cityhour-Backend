<?php

class Application_Model_DbTable_TestCity extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_city';

    public function getList() {
        return $this->_db->fetchPairs($this->_db->select()->from($this->_name, array('id', 'city_name')));
    }

    public function getCity($id) {
        return $this->fetchRow("id = $id");
    }

}

