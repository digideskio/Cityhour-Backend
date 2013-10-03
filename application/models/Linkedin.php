<?php

class Application_Model_Linkedin
{

    public function makePost($token,$id) {
        if (!$slot = (new Application_Model_DbTable_Calendar())->getSlotID($id)) {
            return 404;
        }

        if ($slot['type'] == 1) {

        }
        elseif ($slot['type'] == 2) {

        }

        $params = http_build_query(array(
            'oauth2_access_token' => $token,
            'format' => 'json',
        ));
        $data = "
            <share>
              <comment>Hello Linkedin</comment>
              <content>
                <title>Test share API</title>
                <description>Test description</description>
                <submitted-url>http://google.com</submitted-url>
                <submitted-image-url>http://toplogos.ru/images/logo-chrome.png</submitted-image-url>
              </content>
              <visibility>
                <code>anyone</code>
              </visibility>
            </share>
        ";
        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(CURLOPT_SSL_VERIFYPEER => false),
        );

        $client = new Zend_Http_Client('https://api.linkedin.com/v1/people/~/shares?'.$params,$config);
        $client->setRawData($data,'text/xml');
        $client->setHeaders('Content-Type', 'text/xml');
        $response = $client->request('POST');
//        var_dump($response->getBody());

        return 200;
    }

    public function getUser($token) {
        $params = array(
            'oauth2_access_token' => $token,
            'format' => 'json',
        );
        $url = 'https://api.linkedin.com/v1/people/~:(id,firstName,lastName,email-address,skills,industry,summary,positions,languages,phone-numbers,im-accounts,educations,main-address,location)?' . http_build_query($params);
        $context = stream_context_create(
            array('http' =>
                array(
                    'method' => 'GET',
                )
            )
        );
        try {
            $user_profile = @file_get_contents($url, false, $context);
            $user_profile = json_decode($user_profile,true);
        }
        catch (Exception $e) {
            $user_profile = false;
        }

        if ($user_profile) {
            //Get photo profile
            $url = 'https://api.linkedin.com/v1/people/~/picture-urls::(original)?' . http_build_query($params);
            $context = stream_context_create(
                array('http' =>
                    array(
                        'method' => 'GET',
                    )
                )
            );
            try {
                $picture = @file_get_contents($url, false, $context);
                $picture = json_decode($picture,true);
            }
            catch (Exception $e) {
                $picture = null;
            }
            if (isset($picture['values'][0])) {
                $photo = $picture['values'][0];
            }
            else {
                $photo = null;
            }

            //Industry
            $industry_id = false;
            if (isset($user_profile['industry'])) {
                $industry_id = $user_profile['industry'];
            }

            //get jobs
            $jobs = null;
            if (isset($user_profile['positions']['values'])) {
                foreach ($user_profile['positions']['values'] as $num=>$row) {
                    $start_m =  (isset($row['startDate']['month'])) ? (int)$row['startDate']['month']:1;
                    $end_m =  (isset($row['endDate']['month'])) ? (int)$row['endDate']['month']:1;

                    $jobs[$num] = array(
                        'name' => $row['title'],
                        'company' => (isset($row['company']['name']) && $row['company']['name']) ? $row['company']['name'] : '',
                        'current' => $row['isCurrent'],
                        'start_time' => (isset($row['startDate']['year'])) ? (int)date('Y-m-d',mktime(0,0,0,$start_m,1,(int)$row['startDate']['year'])):null
                    );
                    if ($row['isCurrent']) {
                        $jobs[$num]['end_time'] = null;
                        if (!$industry_id) {
                            $industry_id = $row['company']['industry'];
                        }
                    }
                    else {
                        $jobs[$num]['end_time'] = (isset($row['endDate']['year'])) ? date('Y-m-d',mktime(0,0,0,$end_m,1,(int)$row['endDate']['year'])):null;
                    }
                }
            }

            //get Country ID
            $country = null;
            if (isset($user_profile['location']['country']['code'])) {
                $country = $user_profile['location']['country']['code'];
                $country = (new Application_Model_DbTable_Country())->getName($country);
            }

            //get Industry ID
            $industry = new Application_Model_DbTable_Industries();
            $industry_id = $industry->getID($industry_id);

            //Get phone
            $phone = null;
            if (isset($user_profile['phoneNumbers']['values'][0]['phoneNumber'])) {
                $phone = $user_profile['phoneNumbers']['values'][0]['phoneNumber'];
            }

            //Get skype
            $skype = null;
            if (isset($user_profile['imAccounts']['values'])) {
                foreach ($user_profile['imAccounts']['values'] as $num=>$row) {
                    if ($row['imAccountType']) {
                        $skype = $row['imAccountName'];
                    }
                }
            }

            //Get languages
            $languages = array();
            if (isset($user_profile['languages']['values'])) {
                $language = new Application_Model_DbTable_Languages();
                foreach ($user_profile['languages']['values'] as $num => $row) {
                    $languages[$num] = $language->getID($row['language']['name']);
                }
            }


            //Get skills
            $skills = array();
            if (isset($user_profile['skills']['values'])) {
                foreach ($user_profile['skills']['values'] as $num => $row) {
                    $skills[$num] = $row['skill']['name'];
                }
            }


            //Get education
            $education = array();
            if (isset($user_profile['educations']['values'])) {
                foreach ($user_profile['educations']['values'] as $num => $row) {
                    $education[$num] = array(
                        'name' => (isset($row['fieldOfStudy']) && $row['fieldOfStudy']) ? $row['fieldOfStudy'] : '',
                        'company' => (isset($row['schoolName']) && $row['schoolName']) ? $row['schoolName'] : '',
                        'start_time' => (isset($row['startDate']['year']) && $row['startDate']['year']) ? date('Y-m-d',mktime(0,0,0,1,1,(int)$row['startDate']['year'])) : null,
                        'end_time' => (isset($row['endDate']['year']) && $row['endDate']['year']) ? date('Y-m-d',mktime(0,0,0,1,1,(int)$row['endDate']['year'])) : null
                    );
                }
            }

            // City name
            $city = null;
            if (isset($user_profile['mainAddress'])) {
                $city = $user_profile['mainAddress'];
            }
            elseif (isset($user_profile['location']['name'])) {
                $city = $user_profile['location']['name'];
            }

            // Summary
            $summary = null;
            if (isset($user_profile['summary'])) {
                $summary = $user_profile['summary'];
            }

            return array(
                'name' => $user_profile['firstName'],
                'linkedin_id' => $user_profile['id'],
                'lastname' => $user_profile['lastName'],
                'email' => $user_profile['emailAddress'],
                'photo' => $photo,
                'industry_id' => $industry_id,
                'jobs' => $jobs,
                'summary' => $summary,
                'phone' => $phone,
                'skype' => $skype,
                'languages' => $languages,
                'skills' => $skills,
                'city' => $city,
                'country' => $country,
                'education' => $education
            );
        }
        else {
            return false;
        }
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
            $friends = json_decode($friends, true);
        }
        catch (Exception $e) {
            return false;
        }

        if (!isset($friends['values'])) {
            return false;
        }
        $friends = $friends['values'];
        $db = new Application_Model_DbTable_UserContactsWait();
        $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'user_contacts_wait',
            'field' => 'linkedin_id',
            'exclude' => "user_id = $id"

        ));

        foreach ($friends as $row) {
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

    public function getFriends($token) {
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
            $friends = json_decode($friends, true);
        }
        catch (Exception $e) {
            return false;
        }

        if (isset($friends['values'])) {
            $friends = $friends['values'];
            $users_in = array();
            $res = array();

            foreach ($friends as $num=>$row) {
                if ($row['id'] != 'private') {
                    array_push($users_in,$row['id']);
                }
            }

            $users_in = "'".implode("','",$users_in)."'";
            $db = new Application_Model_DbTable_Users();
            $users = $db->getLinkedinUsers($users_in);
            foreach ($friends as $num=>$row) {
                $user_id = $this->findId($users, $row['id']);
                if (is_numeric($user_id)) {
                    $user_id = $users[$user_id]['id'];
                    if (!isset($row['pictureUrl'])) $row['pictureUrl'] = '';

                    array_push($res,array(
                        'name' => $row['firstName'],
                        'lastname' => $row['lastName'],
                        'linkedin_id' => $row['id'],
                        'id' => $user_id,
                        'status' => 0,
                        'photo' => $row['pictureUrl']
                    ));
                }
            }
        }
        else {
            $res = array();
        }

        return $res;
    }

    private function findId($users, $lid) {
        foreach ($users as $num=>$row) {
            if ($lid == $row['linkedin_id']) {
                return $num;
            }
        }
        return false;
    }
}

