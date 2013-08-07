<?php

require_once(APPLICATION_PATH . '/../library/Facebook/facebook.php');

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

    public function getUser($token) {
        $facebook_config = $this->getConfig();
        $facebook = new Facebook($facebook_config);
        $facebook->setAccessToken($token);
        $user = $facebook->getUser();
        if (!$user) {
            return false;
        }
        $fql = "SELECT uid, first_name, pic_big, last_name FROM user where uid = $user";
        $user_profile = $facebook->api(array(
            'method'       => 'fql.query',
            'access_token' => $token,
            'query'        => $fql,
        ))[0];
        $user_profile['email'] = $facebook->api('/me?fields=email', 'GET')['email'];
        return $user_profile;
    }

    public function storeInfo($token,$id) {
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
        $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'user_contacts_wait',
            'field' => 'linkedin_id',
            'exclude' => "user_id = $id"

        ));
        $db->userUpdateInfo($id,$user,$token,1);

        foreach ($friends as $row) {
            $row = array(
                'name' => $row['first_name'],
                'lastname' => $row['last_name'],
                'linkedin_id' => $row['uid'],
                'photo' => $row['pic'],
                'user_id' => $id,
                'type' => 1
            );
            if ($validator_exist->isValid($row['linkedin_id'])) {
                $db->add($row);
            }
            else {
                $db->updateFacebookData($row,$row['linkedin_id'],$id);
            }
        }
        return true;
    }

}