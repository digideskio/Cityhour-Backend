<?php

class Application_Model_DbTable_Goals extends Zend_Db_Table_Abstract
{

    protected $_name = 'goals';

    public function getList() {
        $res = $this->_db->fetchPairs($this->_db->select()->from($this->_name, array('id', 'name')));
        $res[0] = 'No goal';
        return $res;
    }

}

