<?php

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';

    public function getAll() {
        return $this->fetchAll()->toArray();
    }


    public function registerUser($userData) {
        $id = $this->insert($userData);


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

    public function emailCheck($email)
    {
        $user = $this->fetchRow("email = '$email'");
        if ($user != null) {
            $user = $user->toArray();
            $user['update'] = strtotime($user['update']);
            return $user;
        } else {
            return false;
        }
    }

    public function facebookLogin($token)
    {
        $user = $this->fetchRow("facebook_key = '$token'");
        if ($user != null) {
            $user = $user->toArray();
            $user['update'] = strtotime($user['update']);
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
                            'name' => $user_profile['name'],
                            'facebook_id' => $user_profile['id'],
                            'lastname' => $user_profile['first_name'],
                            'email' => $user_profile['email']
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
            $user = $user->toArray();
            $user['update'] = strtotime($user['update']);
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
                    return array(
                        'body' => array(
                            'name' => $user_profile['firstName'],
                            'linkedin_id' => $user_profile['id'],
                            'lastname' => $user_profile['lastName'],
                            'email' => $user_profile['emailAddress']
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

    public function getUserId($id) {
        $res = $this->fetchRow("id = $id");
        if ($res != null) {
            $res = $res->toArray();
            $res['update'] = strtotime($res['update']);
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
            $res['update'] = strtotime($res['update']);
            return $res;
        }
        else {
            return false;
        }
    }
}

