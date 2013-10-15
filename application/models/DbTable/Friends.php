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
                $this->_db->delete('notifications'," (( `from` = $user_id and `to` = $id ) or ( `to` = $user_id and `from` = $id )) and type = 0");
                Application_Model_Common::updateContacts($user_id.','.$id,false);

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
                    'exclude' => "`to` = $friend_id and type = 0"
                ));
                if ($validator_exist->isValid($user_id)) {
                    $this->_db->beginTransaction();
                    try {
                        $idn = (new Application_Model_DbTable_Notifications())->insertNotification(array(
                            'from' => $user_id,
                            'to' => $friend_id,
                            'item' => $user_id,
                            'type' => 0,
                            'text' => Application_Model_Texts::notification()[0],
                            'template' => 2,
                            'action' => 2
                        ));

                        $text = Application_Model_Texts::push()[4];
                        (new Application_Model_DbTable_Push())->sendPush($friend_id,$text,4,array(
                            'from' => $user_id,
                            'type' => 4,
                            'item' => $idn,
                            'action' => 2
                        ));

                        $this->_db->commit();
                        return true;
                    } catch (Exception $e){
                        $this->_db->rollBack();
                        return false;
                    }
                }
                else {
                    return 301;
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

                $user_id = $user['id'];
                $user_id2 = $notification['from'];

                if ($status == 1) {

                    $friend = $this->_db->fetchRow("
                        select id
                        from user_friends f
                        where (user_id = $user_id and friend_id = $user_id2) or (user_id = $user_id2 and friend_id = $user_id)
                        limit 1
                    ");

                    if ($friend) {
                        $this->_db->commit();
                        return true;
                    }

                    $this->_db->insert('notifications',array(
                        'from' => $user_id,
                        'to' => $user_id2,
                        'item' => $user_id,
                        'type' => 1,
                        'text' => Application_Model_Texts::notification()[1],
                        'template' => 0,
                        'action' => 3
                    ));

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

                    Application_Model_Common::updateContacts($user_id.','.$user_id2,true);
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
