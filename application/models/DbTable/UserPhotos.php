<?php

class Application_Model_DbTable_UserPhotos extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_photos';

    public function makePhoto($filename,$user_id) {
        if (is_numeric($user_id)) {
            $id = $this->fetchRow("user_id = $user_id");
            if ($id != null) {
                $id = $id->toArray();
                $dir = APPLICATION_PATH.'/../public';
                if (is_readable($dir.$id['orig'])) unlink($dir.$id['orig']);

                $id = $id['id'];
                $this->update(array(
                    'orig' => $filename
                ),"id = $id");
                return $id;
            }
        }
        $id = $this->insert(array(
            'orig' => $filename
        ));
        return $id;
    }

}

