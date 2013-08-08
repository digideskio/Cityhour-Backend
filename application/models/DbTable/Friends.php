<?php

class Application_Model_DbTable_Friends extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_friends';


    public function getAll($user) {
        $user_id = $user['id'];

        $res = $this->_db->fetchOne("
            select group_concat(distinct f.friend_id)
            from user_friends f
            where f.user_id = $user_id
            and f.status = 1
        ");

        if ($res) {
            $db = new Application_Model_DbTable_Users();
            return $db->prepeareUsers($res,$user);
        }
        else {
            return array();
        }
    }

    public function deleteFriends($user,$id) {
        $user_id = $user['id'];
        if ($this->fetchRow("user_id = $user_id and friend_id = $id and status = 1")) {
            $this->_db->beginTransaction();

            try {
                $this->delete("(user_id = $user_id and friend_id = $id) or (user_id = $id and friend_id = $user_id)");

                $db = new Application_Model_DbTable_UserContactsWait();
                $db_user = new Application_Model_DbTable_Users();
                $db->updateStatus($user, $db_user->getUser($id,false,'id',false,true), 0);

                $this->_db->commit();
                return true;
            } catch (Exception $e){
                $this->_db->rollBack();
                return $e->getMessage();
            }
        }
        else {
            return 'Not friends';
        }

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

            $che = $this->_db->fetchOne("
                select id
                from notifications
                where
                `from` = $friend_id
                 and `to` = $user_id and type = 0 and status = 0
            ");
            if (!$che) {
                $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'notifications',
                    'field' => 'from',
                    'exclude' => "`to` = $friend_id and type = 0 and status = 0"
                ));
                if ($validator_exist->isValid($user_id)) {
                    $this->_db->beginTransaction();
                    try {
                        $text = 'Friend invite from '.$user['name'];
                        $this->_db->insert('notifications',array(
                            'from' => $user_id,
                            'to' => $friend_id,
                            'type' => 0,
                            'text' => $text
                        ));

                        $db = new Application_Model_DbTable_UserContactsWait();
                        $db->updateStatus($user, $invite, 1);

                        $push = new Application_Model_DbTable_Push();
                        $push->sendPush($friend_id, array(
                            'from' => $user_id,
                            'to' => $friend_id,
                            'type' => 0,
                        ), 0, 'Text alert');
                        $this->_db->commit();
                        return true;
                    } catch (Exception $e){
                        $this->_db->rollBack();
                        return false;
                    }
                }
                else {
                    return true;
                }
            }
            else {
                if ($this->answer($che,2,$user)) {
                    return true;
                }
                return false;
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
            where id = $id and type = 0 and status = 0
        ");
        if ($notification) {
            $this->_db->beginTransaction();
            try {
                $this->_db->update('notifications',array(
                    'status' => 1
                ),"id = $id");
                $db = new Application_Model_DbTable_UserContactsWait();
                $db_user = new Application_Model_DbTable_Users();

                $user_id = $user['id'];
                $user_id2 = $notification['from'];
                $user2 = $db_user->getUser($user_id2,false,'id',false,true);

                if ($status == 2) {
                    $this->insert(array(
                        'user_id' => $user_id2,
                        'friend_id' => $user_id,
                        'status' => 1
                    ));
                    $this->insert(array(
                        'user_id' => $user_id,
                        'friend_id' => $user_id2,
                        'status' => 1
                    ));

                    $db->updateStatus($user, $user2, 2);
                    $this->_db->insert('notifications',array(
                        'from' => $user_id,
                        'to' => $user_id2,
                        'type' => 1,
                        'text' => $user['name'].' Friend request Accept'
                    ));
                }
                elseif ($status == 3) {
                    $db->updateStatus($user, $user2, 0);
                    $this->_db->insert('notifications',array(
                        'from' => $user_id,
                        'to' => $user_id2,
                        'type' => 2,
                        'text' => $user['name'].' Friend request Reject'
                    ));
                }

                $this->_db->commit();
                return true;
            } catch (Exception $e){
                $this->_db->rollBack();
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}
