<?php

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';

    public function updateUser($user,$data) {
        $user_id = $user['id'];
        $this->update($data,"id = $user_id");
        return true;
    }

    public function prepeareUsers($res,$array = true, $user) {
        if ($array) {
            $res = $res->toArray();
        }

        $user_id = $user['id'];
        $friends = $this->_db->fetchOne("
                select group_concat(friend_id)
                from user_friends
                where user_id = $user_id
                and status = 1
            ");
        $friends = explode(',', $friends);
        foreach ($res as $num => $row) {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->userPhoto->url;
            if ($row['photo'] != '' && $row['photo'] != null) {
                $row['photo'] = $url.$row['photo'];
            }
            else {
                $row['photo'] = null;
            }

            $row['update'] = strtotime($row['update']);
            if (in_array($row['id'],$friends)) {
                $row['friend'] = true;
            }
            else {
                $row['friend'] = false;
            }

            $res[$num] = $row;
        }
        return $res;
    }

    public function getAll($user) {
        $user_id = $user['id'];
        $res = $this->fetchAll("id != $user_id");
        if ($res != null) {
            $res = $this->prepeareUsers($res, true, $user);
            return $res;
        }
        else {
            return array();
        }

    }

    public function registerUser($userData) {
        if (is_numeric($userData['photo'])) {
            $photo_id = $userData['photo'];
            $userData['photo'] = $this->_db->fetchOne("
                select orig
                from user_photos
                where id = $photo_id
            ");
            $id = $this->insert($userData);
            $this->_db->update('user_photos',array(
                'user_id' => $id
            ),"id = $photo_id");
        }
        else {
            $id = $this->insert($userData);
        }

        if (isset($userData['facebook_key'])) {
            $facebook = new Application_Model_Facebook();
            $facebook->storeInfo($userData['facebook_key'],$id);
        }

        if (isset($userData['linkedin_key'])) {
            $facebook = new Application_Model_Linkedin();
            $facebook->storeInfo($userData['linkedin_key'],$id);
        }

        return $id;
    }

    public function facebookLogin($token)
    {
        $user = $this->fetchRow("facebook_key = '$token'");
        if ($user != null) {
            $user = $this->prepeareUser($user);
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            $facebook = new Application_Model_Facebook();
            $user_profile = $facebook->getUser($token);
            if ($user_profile) {
                $user = $this->emailCheck($user_profile['email']);
                if ($user) {
                    $user['update'] = strtotime($user['update']);
                    $id = $user['id'];
                    $this->update(array(
                        'facebook_key' => $token,
                        'facebook_id' => $user_profile['id']
                    ),"id = $id");

                    return array(
                        'body' => $user,
                        'errorCode' => '200'
                    );
                }
                else {
                    return array(
                        'body' => array(
                            'name' => $user_profile['first_name'],
                            'facebook_id' => $user_profile['uid'],
                            'lastname' => $user_profile['last_name'],
                            'email' => $user_profile['email'],
                            'photo' => $user_profile['pic_big']
                        ),
                        'errorCode' => '404'
                    );
                }
            } else {
                return array(
                    'errorCode' => '405'
                );
            }
        }
    }

    public function linkedinLogin($token)
    {
        $user = $this->fetchRow("linkedin_key = '$token'");
        if ($user != null) {
            $user = $this->prepeareUser($user);
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            $linkedin = new Application_Model_Linkedin();
            $user_profile = $linkedin->getUser($token);
            if ($user_profile) {
                $user = $this->emailCheck($user_profile['emailAddress']);
                if ($user) {
                    $user['update'] = strtotime($user['update']);
                    $id = $user['id'];
                    $this->update(array(
                        'linkedin_key' => $token,
                        'linkedin_id' => $user_profile['id']
                    ),"id = $id");

                    return array(
                        'body' => $user,
                        'errorCode' => '200'
                    );
                }
                else {
                    if (!isset($user_profile['pictureUrl'])) $user_profile['pictureUrl'] = '';
                    return array(
                        'body' => array(
                            'name' => $user_profile['firstName'],
                            'linkedin_id' => $user_profile['id'],
                            'lastname' => $user_profile['lastName'],
                            'email' => $user_profile['emailAddress'],
                            'photo' => $user_profile['photo']
                        ),
                        'errorCode' => '404'
                    );
                }
            } else {
                return array(
                    'errorCode' => '405'
                );
            }
        }
    }

    public function userCheck($token)
    {
        if ($this->fetchRow("private_key = '$token'") != null) {
            return true;
        } else {
            return false;
        }
    }

    public function prepeareUser($res,$array = true, $user = false) {
        if ($array) {
            $res = $res->toArray();
        }
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;
        if ($res['photo'] != '' && $res['photo'] != null) {
            $res['photo'] = $url.$res['photo'];
        }
        else {
            $res['photo'] = null;
        }
        if ($user) {
            $user_id = $user['id'];
            $friend_id = $res['id'];
            $friends = $this->_db->fetchOne("
                select group_concat(friend_id)
                from user_friends
                where user_id = $user_id
                and status = 1
                and friend_id = $friend_id
            ");
            if ($friends) {
                $res['friend'] = true;
            }
            else {
                $res['friend'] = false;
            }
        }
        return $res;
    }

    public function getUser($id,$user) {
        $res = $this->fetchRow("id = $id");
        if ($res != null) {
            $res = $this->prepeareUser($res,true,$user);
            return $res;
        }
        else
            return false;
    }

    public function emailCheck($email)
    {
        $res = $this->fetchRow("email = '$email'");
        if ($res != null) {
            $res = $this->prepeareUser($res);
            return $res;
        } else {
            return false;
        }
    }

    public function getUserId($id) {
        $res = $this->fetchRow("id = $id");
        if ($res != null) {
            $res = $this->prepeareUser($res);
            return $res;
        }
        else {
            return false;
        }
    }

    public static function getUserData($private_key) {
        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select *
            from users
            where private_key = '$private_key'
        ");
        if ($res != null) {
            $db = new Application_Model_DbTable_Users();
            $res = $db->prepeareUser($res,false);
            return $res;
        }
        else {
            return false;
        }
    }
}

