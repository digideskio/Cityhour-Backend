<?php

class Application_Model_Linkedin
{

    public function getUser($token) {
        $params = array('oauth2_access_token' => $token,
            'format' => 'json',
        );
        $url = 'https://api.linkedin.com/v1/people/~:(id,firstName,lastName,email-address,skills,industry,summary,positions,languages,phone-numbers,im-accounts,educations,main-address)?' . http_build_query($params);
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
            $user_profile['photo'] = $picture['values'][0];

            //get jobs
            $jobs = null;
            foreach ($user_profile['positions']['values'] as $num=>$row) {
                $jobs[$num] = array(
                    'name' => $row['title'],
                    'company' => $row['company']['name'],
                    'current' => $row['isCurrent'],
                    'start_time' => date('Y-m-d',mktime(0,0,0,(int)$row['startDate']['month'],1,(int)$row['startDate']['year']))
                );
                if ($row['isCurrent']) {
                    $jobs[$num]['end_time'] = null;
                    $user_profile['industry'] = $row['company']['industry'];
                }
                else {
                    $jobs[$num]['end_time'] = date('Y-m-d',mktime(0,0,0,(int)$row['endDate']['month'],1,(int)$row['endDate']['year']));
                }
            }

            //get Industry ID
            $industry = new Application_Model_DbTable_Industries();
            $user_profile['industry'] = $industry->getID($user_profile['industry']);

            //Get phone
            $phone = null;
            if (isset($user_profile['phoneNumbers']['values'][0]['phoneNumber'])) {
                $phone = $user_profile['phoneNumbers']['values'][0]['phoneNumber'];
            }

            //Get skype
            $skype = null;
            foreach ($user_profile['imAccounts']['values'] as $num=>$row) {
                if ($row['imAccountType']) {
                    $skype = $row['imAccountName'];
                }
            }

            //Get languages
            $languages = array();
            $language = new Application_Model_DbTable_Languages();
            foreach ($user_profile['languages']['values'] as $num => $row) {
                $languages[$num] = $language->getID($row['language']['name']);
            }

            //Get skills
            $skills = array();
            foreach ($user_profile['skills']['values'] as $num => $row) {
                $skills[$num] = $row['skill']['name'];
            }

            //Get education
            $education = array();
            foreach ($user_profile['educations']['values'] as $num => $row) {
                $education[$num] = array(
                    'name' => $row['fieldOfStudy'],
                    'company' => $row['schoolName'],
                    'start_time' => date('Y-m-d',mktime(0,0,0,1,1,(int)$row['startDate']['year'])),
                    'end_time' => date('Y-m-d',mktime(0,0,0,1,1,(int)$row['endDate']['year']))
                );
            }

            return array(
                'name' => $user_profile['firstName'],
                'linkedin_id' => $user_profile['id'],
                'lastname' => $user_profile['lastName'],
                'email' => $user_profile['emailAddress'],
                'photo' => $user_profile['photo'],
                'industry_id' => $user_profile['industry'],
                'jobs' => $jobs,
                'summary' => $user_profile['summary'],
                'phone' => $phone,
                'skype' => $skype,
                'languages' => $languages,
                'skills' => $skills,
                'city' => $user_profile['mainAddress'],
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

        if ($friends == null) {
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
            $users = $db->fetchAll("linkedin_id in ($users_in)")->toArray();
            foreach ($friends as $num=>$row) {
                $user_id = $this->findId($users, $row['id']);
                if ($user_id) {
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
            if (in_array($lid, $row)) {
                return $num;
            }
        }
        return false;
    }
}

