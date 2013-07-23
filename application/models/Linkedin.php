<?php

class Application_Model_Linkedin
{

    public function getUser($token) {
        $params = array('oauth2_access_token' => $token,
            'format' => 'json',
        );
        $url = 'https://api.linkedin.com/v1/people/~:(id,firstName,lastName,email-address)?' . http_build_query($params);
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



        $url = 'https://api.linkedin.com/v1/people/~/picture-urls::(original)?' . http_build_query($params);
        $context = stream_context_create(
            array('http' =>
            array('method' => 'GET',
            )
            )
        );
        try {
            $picture = @file_get_contents($url, false, $context);
            $picture = (array)json_decode($picture);
        }
        catch (Exception $e) {
            $picture = null;
        }

        $user_profile['photo'] = $picture['values'][0];
        return $user_profile;
    }

    public function storeInfo($token,$id) {

        $params = array('oauth2_access_token' => $token,
            'format' => 'json',
        );
        $url = 'https://api.linkedin.com/v1/people/~/connections:(id,firstName,lastName,picture-url)?' . http_build_query($params);
        $context = stream_context_create(
            array('http' =>
            array('method' => 'GET',
            )
            )
        );
        try {
            $friends = @file_get_contents($url, false, $context);
            $friends = (array)json_decode($friends);
        }
        catch (Exception $e) {
            return false;
        }

        if ($friends == null) {
            return false;
        }
        $friends = (array)$friends['values'];
        $db = new Application_Model_DbTable_UserContactsWait();
        $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'user_contacts_wait',
            'field' => 'linkedin_id',
            'exclude' => "user_id = $id"

        ));
        foreach ($friends as $row) {
            $row = (array)$row;
            if ($row['id'] != 'private') {
                if (!isset($row['pictureUrl'])) $row['pictureUrl'] = '';

                $row = array(
                    'name' => $row['firstName'],
                    'lastname' => $row['lastName'],
                    'linkedin_id' => $row['id'],
                    'photo' => $row['pictureUrl'],
                    'user_id' => $id,
                    'type' => 2
                );
                if ($validator_exist->isValid($row['linkedin_id'])) {
                    $db->add($row);
                }
                else {
                    $db->updateLinkedinData($row,$row['linkedin_id'],$id);
                }
            }
        }
        return true;
    }

}

