<?php

class Application_Model_DbTable_Industries extends Zend_Db_Table_Abstract
{

    protected $_name = 'industries';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }

}

