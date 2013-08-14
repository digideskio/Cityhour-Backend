<?php

class Application_Model_DbTable_UserPhotos extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_photos';

    public function makePhoto($filename,$private_key,$config,$tmp_file) {
        $s3 = new Zend_Service_Amazon_S3($config['my_aws_key'], $config['my_aws_secret_key']);
        $s3->putFile($tmp_file, $config['bucket'].$filename,
            array(
                Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => 'image/jpeg'
            )
        );

        $private_key = Application_Model_DbTable_Users::getUserData($private_key);
        if ($private_key) {
            $user_id = $private_key['id'];
            $id = $this->fetchRow("user_id = $user_id");
            if ($id != null) {
                $id = $id->toArray();
                $s3->removeObject($config['bucket'].$id['orig']);

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

