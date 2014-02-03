<?php

class Application_Model_DbTable_Industries extends Zend_Db_Table_Abstract
{

    protected $_name = 'industries';

    public function getList() {
        $res = $this->_db->fetchPairs($this->_db->select()->from($this->_name, array('id', 'name')));
        $res[0] = 'No industry';
        return $res;
    }

}

