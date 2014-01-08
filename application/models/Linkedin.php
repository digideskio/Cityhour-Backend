<?php

class Application_Model_Linkedin
{

    public function makePost($user,$id) {
        $token = $user['linkedin_key'];
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
        $user_id = $user['id'];
        $slot_id = $slot['id'];
        $start_time = $slot["start_time"] + $slot["offset"];
        $start_time = gmdate('m/d/Y H:i:s', (int)$start_time);
        $with = (new Application_Model_DbTable_Users)->getUser($slot['user_id_second'],$user,'id',false,false);
        $with = $with['name'].' '.substr($with['lastname'],0,1).'.';

        $data = "
            <share>
              <comment>I just supercharged my network and booked a meeting with $with from $start_time using the CityHour app!</comment>
              <content>
                <title>Cityhour</title>
                <description>Cityhour</description>
                <submitted-url>http://cityhour.com/meeting/?uid=$user_id&amp;id=$slot_id</submitted-url>
                <submitted-image-url>http://cityhour.com/site/img/logo.png</submitted-image-url>
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
        $client->request('POST');

        return 200;
    }


    public function updateUser($user,$config) {
        if ($user_linkedin = $this->getUser($user['linkedin_key'])) {
            if ($user_linkedin['photo']) {
                $rrr = uniqid(time(), false);
                $tmp_name = '/tmp/tmp_'.$rrr.'.jpg';
                $client = new Zend_Http_Client($user_linkedin['photo']);
                file_put_contents($tmp_name,$client->request('GET')->getBody());

                $ext = pathinfo($tmp_name);
                $filename = 'userPic_'.$rrr.'.'.$ext['extension'];

                (new Application_Model_DbTable_UserPhotos())->makePhoto($rrr,$filename,$user['private_key'],$config,$tmp_name);
            }
            (new Application_Model_DbTable_Users())->updateUser($user,$user_linkedin,true);
            return true;
        }
        else {
            Zend_Controller_Action_HelperBroker::getStaticHelper('json')->sendJson(array(
                'errorCode' => '405'
            ));
        }
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
            $jobs = array();
            $last_job = false;
            if (isset($user_profile['positions']['values'])) {
                foreach ($user_profile['positions']['values'] as $num=>$row) {
                    $start_m =  (isset($row['startDate']['month'])) ? (int)$row['startDate']['month']:1;
                    $end_m =  (isset($row['endDate']['month'])) ? (int)$row['endDate']['month']:1;

                    $jobs[$num] = array(
                        'name' => $row['title'],
                        'company' => (isset($row['company']['name']) && $row['company']['name']) ? $row['company']['name'] : '',
                        'current' => 0,
                        'active' => $row['isCurrent'],
                        'start_time' => (isset($row['startDate']['year'])) ? date('Y-m-d',mktime(0,0,0,$start_m,1,(int)$row['startDate']['year'])):null
                    );
                    if ($row['isCurrent']) {
                        $last_job = $num;
                        $jobs[$num]['end_time'] = null;
                        if (!$industry_id) {
                            $industry_id = $row['company']['industry'];
                        }
                    }
                    else {
                        $jobs[$num]['end_time'] = (isset($row['endDate']['year'])) ? date('Y-m-d',mktime(0,0,0,$end_m,1,(int)$row['endDate']['year'])):null;
                    }
                }
                if ($last_job) {
                    $jobs[$last_job]['current'] = 1;
                }
                else {
                    $jobs[0]['current'] = 1;
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
                    if (isset($row['imAccountType']) && $row['imAccountType'] == 'skype') {
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
                        'current' => 0,
                        'active' => 0,
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
                'city_name' => $city,
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

        $validator_exist = $db->getByUser($id);

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
                if (!in_array($row['linkedin_id'],$validator_exist)) {
                    $db->add($row);
                }

                // If will need
//                else {
//                    $db->updateLinkedinData($row,$row['linkedin_id'],$id);
//                }
            }
        }
        return true;
    }

    public function getFriends($token,$registration = false) {
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
            if ($registration) {
                $users_ids = array();
                $b = array();
                foreach ($res as $row) {
                    array_push($users_ids,$row['id']);
                    $b['z'.$row['id']] = $row;
                }
                if ($users_ids) {
                    $jobs = $db->getUserJobs($users_ids);

                    $a = array();
                    foreach ($jobs as $row) {
                        $a['z'.$row['user_id']] = array(
                            'company' => $row['company'],
                            'job' => $row['name'],
                        );
                    }

                    $res = array_merge_recursive($a, $b);
                    $res = array_values($res);
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

