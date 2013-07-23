<?php

class Application_Model_DbTable_Friends extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_friends';


    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->_db->fetchAll("
            select u.*
            from user_friends f
            left join users u on f.friend_id = u.id
            where f.user_id = $user_id
            and f.status = 1
        ");
        if ($res != null) {
            return $res;
        }
        else {
            return array();
        }
    }

    public function deleteFriends($user,$id) {
        $user_id = $user['id'];
        $data = array(
            'status' => 2
        );
        $this->update($data,"(user_id = $user_id and friend_id = $id) or (user_id = $id and friend_id = $user_id)");
        return true;
    }

    public function addFriend($fid, $user) {
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
                $db = new Application_Model_DbTable_UserContactsWait();
                $db->updateStatus($user, $invite, 2);
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

    public function answer($id, $status, $user) {
        $notification = $this->_db->fetchRow("
            select *
            from notifications
            where id = $id and type = 0 and status = 1
        ");
        if ($notification) {
            $this->update(array(
                'status' => $status
            ),"id = $id");

            $user_id = $user['id'];
            $db = new Application_Model_DbTable_UserContactsWait();
            $db_user = new Application_Model_DbTable_Users();
            $user2 = $db_user->getUserId($id);
            if ($status == 2) {
                $this->insert(array(
                    'user_id' => $id,
                    'friend_id' => $user_id,
                    'status' => 1
                ));
                $this->insert(array(
                    'user_id' => $user_id,
                    'friend_id' => $id,
                    'status' => 1
                ));
                $db->updateStatus($user, $user2, 2);
            }
            elseif ($status == 3) {
                $db->updateStatus($user, $user2, 3);
            }

            return true;
        }
        else {
            return false;
        }
    }
}
