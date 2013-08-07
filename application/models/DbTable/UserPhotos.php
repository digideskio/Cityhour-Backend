<?php

class Application_Model_DbTable_UserPhotos extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_photos';

    public function makePhoto($filename,$private_key) {
        $private_key = Application_Model_DbTable_Users::getUserData($private_key);
        if ($private_key) {
            $user_id = $private_key['id'];
            $id = $this->fetchRow("user_id = $user_id");
            if ($id != null) {
                $id = $id->toArray();
                $dir = APPLICATION_PATH.'/../public';
                if (is_readable($dir.$id['orig'])) unlink($dir.$id['orig']);

                $id = $id['id'];
                $this->update(array(
                    'orig' => $filename
                ),"id = $id");
                $this->_db->update('users',array(
                    'photo' => $filename
                ),"id = $user_id");
                return $id;
            }
        }
        $id = $this->insert(array(
            'orig' => $filename
        ));
        return $id;
    }

}

