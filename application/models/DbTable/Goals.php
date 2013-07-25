<?php

class Application_Model_DbTable_Goals extends Zend_Db_Table_Abstract
{

    protected $_name = 'goals';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }

}