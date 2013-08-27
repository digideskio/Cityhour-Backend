<?php

class Application_Model_DbTable_UserPhotos extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_photos';

    public function makePhoto($filename_o,$filename,$private_key,$config,$tmp_file) {
        $filename_o = 'circle_'.$filename_o.'.png';
        $filename_ot = '/tmp/'.$filename_o;

        $size = getimagesize($tmp_file);
        $width_h = $size[0]/2;
        $height_h = $size[1]/2;

        exec("/opt/local/bin/convert -size $size[0]x$size[1] xc:none -fill $tmp_file -draw \"circle $width_h,$height_h $width_h,1\" $filename_ot");

        $s3 = new Zend_Service_Amazon_S3($config['my_aws_key'], $config['my_aws_secret_key']);
        $s3->putFile($tmp_file, $config['bucket'].$filename,
            array(
                Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => 'image/png'
            )
        );
        $s3->putFile($filename_ot, $config['bucket'].$filename_o,
            array(
                Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => 'image/png'
            )
        );

        $private_key = Application_Model_DbTable_Users::getUserData($private_key);
        if ($private_key) {
            $user_id = $private_key['id'];
            $id = $this->fetchRow("user_id = $user_id");
            if ($id != null) {
                $id = $id->toArray();
                $s3->removeObject($config['bucket'].$id['orig']);
                $s3->removeObject($config['bucket'].$id['circle_260']);

                $id = $id['id'];
                $this->update(array(
                    'orig' => $filename,
                    'circle_260' => $filename_o
                ),"id = $id");
                $this->_db->update('users',array(
                    'photo' => $filename_o
                ),"id = $user_id");
                return $id;
            }
        }
        $id = $this->insert(array(
            'orig' => $filename,
            'circle_260' => $filename_o
        ));
        return $id;
    }

}

