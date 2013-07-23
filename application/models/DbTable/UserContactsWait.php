<?php

class Application_Model_DbTable_UserContactsWait extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_contacts_wait';

    public function add($data) {
        $this->insert($data);
        return true;
    }

    public function updateFacebookData($data,$id, $user_id) {
        $this->update($data,"facebook_id = '$id' and user_id = $user_id");
        return true;
    }

    public function updateLinkedinData($data,$id, $user_id) {
        $this->update($data,"linkedin_id = '$id' and user_id = $user_id");
        return true;
    }

    public function getAll($user, $type) {
        if ($type == 1) {
            if ($user['facebook_key'] && $user['facebook_key'] != null && $user['facebook_key'] != '') {
                $facebook = new Application_Model_Facebook();
                $facebook->storeInfo($user['facebook_key'],$user['id']);
                $id = $user['id'];
                $res = $this->_db->fetchAll("
                    select u.id, w.name, w.lastname, w.photo, w.status
                    from users u
                    left join user_contacts_wait w on u.facebook_id = w.facebook_id
                    where
                      user_id = $id and type = 1
                ");
            }
            else {
                $res = array();
            }
        }
        else if ($type == 2) {
            if ($user['linkedin_key'] && $user['linkedin_key'] != null && $user['linkedin_key'] != '') {
                $linkedin = new Application_Model_Linkedin();
                $linkedin->storeInfo($user['linkedin_key'],$user['id']);
                $id = $user['id'];
                $res = $this->_db->fetchAll("
                    select u.id, w.name, w.lastname, w.photo, w.status
                    from users u
                    left join user_contacts_wait w on u.linkedin_id = w.linkedin_id
                    where
                      user_id = $id and type = 2
                ");
            }
            else {
                $res = array();
            }
        }
        if (isset($res) && $res != null) {
            return $res;
        }
        else {
            return array();
        }
    }

    public function invite($fid, $user) {
        $invite = $this->_db->fetchRow("
            select *
            from users
            where id = $fid
        ");
        if ($invite != null) {
            $user_id = $user['id'];
            $friend_id = $invite['id'];
            $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
                'table' => 'notifications',
                'field' => 'from',
                'exclude' => "`to` = $friend_id and type = 0"
            ));

            if ($validator_exist->isValid($user_id)) {
                $this->_db->insert('notifications',array(
                    'from' => $user_id,
                    'to' => $friend_id,
                    'type' => 0,
                    'text' => 'Friend invite Хочешь не хочешь?'
                ));

                $facebook_id = $invite['facebook_id'];
                $linkedin_id = $invite['linkedin_id'];
                $this->update(array(
                    'status' => 1
                ),"user_id = $user_id and (facebook_id = '$facebook_id' or linkedin_id = '$linkedin_id')");
                return true;
            }
            else {
                return true;
            }
        }
        else {
            return false;
        }
    }

}