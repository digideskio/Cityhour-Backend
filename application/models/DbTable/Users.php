<?php

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';

    public function facebookCheck($token)
    {
        if ($this->fetchRow("facebook_key = '$token'") != null) {
            return true;
        } else {
            return false;
        }
    }

    public function linkedinCheck($token)
    {
        if ($this->fetchRow("linkedin_key = '$token'") != null) {
            return true;
        } else {
            return false;
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

    public function getUser($token,$type = false) {
        if ($type) {
            $type = Application_Model_Types::getLogin()[$type].'_key';
        }
        else {
            $type = 'private_key';
        }
        $res = $this->fetchRow("$type = '$token'")->toArray();
        if ($res != null) {
            return $res;
        }
        else {
            return false;
        }
    }

    public function getUserLinkedin($token, $be)
    {
        if ($be) {
            $user = $this->getUser($token,2);
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            $params = array('oauth2_access_token' => $token,
                'format' => 'json',
            );
            $url = 'https://api.linkedin.com/v1/people/~:(firstName,lastName,email-address)?' . http_build_query($params);
            $context = stream_context_create(
                array('http' =>
                array('method' => 'GET',
                )
                )
            );
            try {
                $user_profile = @file_get_contents($url, false, $context);
                $user_profile = (array)json_decode($user_profile);
            }
            catch (Exception $e) {
                $user_profile = false;
            }
            if ($user_profile != false) {
                return array(
                    'body' => array(
                        'name' => $user_profile['firstName'],
                        'lastname' => $user_profile['lastName'],
                        'email' => $user_profile['emailAddress']
                    ),
                    'errorCode' => '404'
                );
            } else {
                return array(
                    'errorCode' => '405'
                );
            }
        }
    }

    public function getUserFacebook($token, $be, $config)
    {
        if ($be) {
            $user = $this->getUser($token,1);
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            require_once(APPLICATION_PATH . '/../library/Facebook/facebook.php');
            $facebook_config = array(
                'appId' => $config['appId'],
                'secret' => $config['secret']
            );
            $facebook = new Facebook($facebook_config);
            $facebook->setAccessToken($token);
            $user_id = $facebook->getUser();
            if ($user_id) {
                $user_profile = $facebook->api('/me', 'GET');
                return array(
                    'body' => array(
                        'name' => $user_profile['name'],
                        'lastname' => $user_profile['first_name'],
                        'email' => $user_profile['email']
                    ),
                    'errorCode' => '404'
                );
            } else {
                return array(
                    'errorCode' => '405'
                );
            }
        }
    }

}

