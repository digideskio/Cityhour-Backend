<?php

class Application_Model_DbTable_UserPhotos extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_photos';

    public function makePhoto($filename) {
        $id = $this->insert(array(
            'orig' => $filename
        ));
        return $id;
    }

}

