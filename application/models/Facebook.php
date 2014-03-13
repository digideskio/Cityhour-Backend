<?php

require_once(APPLICATION_PATH.'/../vendor/facebook/php-sdk/src/facebook.php');

class Application_Model_Facebook
{

    public function getConfig() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $appId = $config->facebook->appId;
        $secret = $config->facebook->secret;
        return $facebook_config = array(
            'appId' => $appId,
            'secret' => $secret
        );
    }

    public function updateUser($user,$config) {
        if ($user_facebook = $this->getUser($user['facebook_key'])) {
            if ($user_facebook['photo']) {
                $rrr = uniqid(time(), false);
                $tmp_name = '/tmp/tmp_'.$rrr.'.jpg';
                $client = new Zend_Http_Client($user_facebook['photo']);
                file_put_contents($tmp_name,$client->request('GET')->getBody());

                $ext = pathinfo($tmp_name);
                $filename = 'userPic_'.$rrr.'.'.$ext['extension'];

                (new Application_Model_DbTable_UserPhotos())->makePhoto($rrr,$filename,$user['private_key'],$config,$tmp_name);
            }
            (new Application_Model_DbTable_Users())->updateUser($user,$user_facebook,false,true);
            return true;
        }
        else {
            Zend_Controller_Action_HelperBroker::getStaticHelper('json')->sendJson(array(
                'errorCode' => '405'
            ));
        }
    }

    public function getUser($token) {
        $facebook_config = $this->getConfig();
        $facebook = new Facebook($facebook_config);
        $facebook->setAccessToken($token);
        $user = $facebook->getUser();
        if (!$user) {
            return false;
        }
        $fql = "SELECT uid, first_name, pic_big, last_name, about_me, education,current_location,languages, work FROM user where uid = $user";
        $user_profile = @$facebook->api(array(
            'method'       => 'fql.query',
            'access_token' => $token,
            'query'        => $fql,
        ))[0];
        $user_profile['email'] = @$facebook->api('/me?fields=email', 'GET')['email'];

        if ($user_profile) {

            //get jobs
            $jobs = array();
            $last_job = false;
            $last_job_any = false;
            if (isset($user_profile['work'])) {
                foreach ($user_profile['work'] as $num => $row) {
                    $start_m = (isset($row['start_date'])) ? date('Y-m-d',strtotime($row['start_date'])) : null;
                    $end_m = (isset($row['end_date'])) ? date('Y-m-d',strtotime($row['end_date'])) : null;

                    if ($start_m && $end_m) {
                        $jobs[$num] = array(
                            'name' => (isset($row['position']['name']) && $row['position']['name']) ? $row['position']['name'] : '',
                            'company' => (isset($row['employer']['name']) && $row['employer']['name']) ? $row['employer']['name'] : '',
                            'current' => 0,
                            'active' => 0,
                            'start_time' => $start_m,
                            'end_time' => $end_m
                        );
                        if (!$end_m) {
                            $last_job = $num;
                            $jobs[$num]['active'] = 1;
                        }
                        $last_job_any = $num;
                    }

                }
                if ($last_job) {
                    $jobs[$last_job]['current'] = 1;
                } elseif ($jobs && isset($jobs[0])) {
                    $jobs[0]['current'] = 1;
                } elseif ($jobs) {
                    $last_job_any = $last_job_any-1;
                    $jobs[$last_job_any]['current'] = 1;
                }
            }

            //Get Country ID
            $country = null;
            if (isset($user_profile['current_location']['country'])) {
                $country = $user_profile['current_location']['country'];
            }

            //Get languages
            $languages = array();
            if (isset($user_profile['languages'])) {
                $language = new Application_Model_DbTable_Languages();
                foreach ($user_profile['languages'] as $num => $row) {
                    if ($lng = $language->getID($row['name'])) {
                        $languages[$num] = $lng;
                    }
                }
            }


            //Get education
            $education = array();
            if (isset($user_profile['education'])) {
                foreach ($user_profile['education'] as $num => $row) {
                    $education[$num] = array(
                        'name' => '',
                        'company' => (isset($row['school']['name']) && $row['school']['name']) ? $row['school']['name'] : '',
                        'current' => 0,
                        'active' => 0,
                        'start_time' => null,
                        'end_time' => null
                    );
                }
            }

            // City name
            $city = null;
            if (isset($user_profile['current_location']['city'])) {
                $city = $user_profile['current_location']['city'];
            }

            $res = array(
                'name' => $user_profile['first_name'],
                'facebook_id' => $user_profile['uid'],
                'lastname' => $user_profile['last_name'],
                'email' => $user_profile['email'],
                'photo' => $user_profile['pic_big'],
                'industry_id' => 200,
                'jobs' => $jobs,
                'summary' => $user_profile['about_me'],
                'phone' => null,
                'skype' => null,
                'languages' => $languages,
                'skills' => array(),
                'city_name' => $city,
                'country' => $country,
                'education' => $education
            );
            return $res;
        } else {
            return false;
        }
    }

    public function storeInfo($token,$id,$user_real) {
        $facebook_config = $this->getConfig();
        $facebook = new Facebook($facebook_config);
        $facebook->setAccessToken($token);
        $user = $facebook->getUser();
        if (!$user) {
            return false;

        }

        $fql = "SELECT uid, first_name, pic, last_name FROM user "
            . "WHERE uid in (SELECT uid2 FROM friend where uid1 = $user)";
        $friends = $facebook->api(array(
            'method'       => 'fql.query',
            'access_token' => $token,
            'query'        => $fql,
        ));

        $db = new Application_Model_DbTable_UserContactsWait();
        $validator_exist = $db->getByUser($id,1);
        $db->userUpdateInfo($id,$user,$token,1,$user_real);

        foreach ($friends as $row) {
            $row = array(
                'name' => $row['first_name'],
                'lastname' => $row['last_name'],
                'linkedin_id' => $row['uid'],
                'photo' => $row['pic'],
                'user_id' => $id,
                'type' => 1
            );
            if (!in_array($row['linkedin_id'],$validator_exist)) {
                $db->add($row);
            }
        }
        return true;
    }

    public function getFriends($token) {
        $facebook_config = $this->getConfig();
        $facebook = new Facebook($facebook_config);
        $facebook->setAccessToken($token);
        $user = $facebook->getUser();
        if (!$user) {
            return false;
        }

        try {
            $fql = "select uid from user where uid in (select uid2 from friend where uid1=me()) and is_app_user=1";
            $friends = $facebook->api(array(
                'method'       => 'fql.query',
                'access_token' => $token,
                'query'        => $fql,
            ));
        } catch (Exception $e) {
            return false;
        }

        if (!$friends) {
            return false;
        }

        $res = array();
        foreach ($friends as $row) {
            $res[]= $row['uid'];
        }
        if ($res) {
            $res = implode(',',$res);
            $res = (new Application_Model_DbTable_UserContactsWait())->getFacebookFriends($res);
        }

        return $res;
    }

}