<?php

class Application_Model_DbTable_TestCalendar extends Zend_Db_Table_Abstract
{

    protected $_name = 'test_calendar';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }

    public function getEvent($id) {
        return $this->fetchRow("id = $id");
    }

    public function saveEvent($formData,$id) {
        return true;
    }

    public function addEvent($formData) {
        return true;
    }

}

