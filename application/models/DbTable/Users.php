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

    public function getPeople($user,$data_from,$data_to,$city,$industry,$goals) {
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

    public function registerUser($data) {
        $filter = new Zend_Filter_StripTags();

        //Important
        $userData = array(
            'email' => $data['email'],
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'industry_id' => $data['industry_id'],
            'private_key' => uniqid(sha1(time()), false)
        );

        //Not Important
        if (isset($data['skype'])) $userData['skype'] = $filter->filter($data['skype']);
        if (isset($data['summary'])) $userData['summary'] = $filter->filter($data['summary']);
        if (isset($data['phone'])) $userData['phone'] = $filter->filter($data['phone']);
        if (isset($data['business_email'])) $userData['business_email'] = $filter->filter($data['business_email']);

        if (isset($data['facebook_key'])) $userData['facebook_key'] =  $filter->filter($data['facebook_key']);
        if (isset($data['facebook_id'])) $userData['facebook_id'] =  $filter->filter($data['facebook_id']);
        if (isset($data['linkedin_key'])) $userData['linkedin_key'] = $filter->filter($data['linkedin_key']);
        if (isset($data['linkedin_id'])) $userData['linkedin_id'] = $filter->filter($data['linkedin_id']);


        if (isset($data['city'])) {
            $userData = array_merge($userData,Application_Model_Common::getCity($data['city']));
        }

        $id = $this->insert($userData);

        foreach($data['skills'] as $num=>$row) {
            $this->_db->insert('user_skills',array(
                'user_id' => $id,
                'name' => $row,
            ));
        }

        foreach($data['languages'] as $num=>$row) {
            $this->_db->insert('user_languages',array(
                'user_id' => $id,
                'languages_id' => $row,
            ));
        }

        $experience = 0;
        foreach($data['jobs'] as $num=>$row) {
            $this->_db->insert('user_jobs',array(
                'user_id' => $id,
                'name' => $row['name'],
                'company' => $row['company'],
                'current' => $row['current'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'type' => 0
            ));
            $d1 = date_create($row['start_time']);
            $d2 = date_create($row['end_time']);
            $experience = $experience + $d1->diff($d2)->m;
        }

        foreach($data['education'] as $num=>$row) {
            $this->_db->insert('user_jobs',array(
                'user_id' => $id,
                'name' => $row['name'],
                'company' => $row['company'],
                'current' => 0,
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'type' => 1
            ));
        }

        if (isset($userData['facebook_key'])) {
            $facebook = new Application_Model_Facebook();
            $facebook->storeInfo($userData['facebook_key'],$id);
        }

        if (isset($userData['linkedin_key'])) {
            $facebook = new Application_Model_Linkedin();
            $facebook->storeInfo($userData['linkedin_key'],$id);
        }

        $photo = null;
        if (isset($data['photo_id']) && is_numeric($data['photo_id'])) {
            $photo_id = $data['photo_id'];
            $photo = $this->_db->fetchOne("
                select orig
                from user_photos
                where id = $photo_id
            ");
            $this->_db->update('user_photos',array(
                'user_id' => $id
            ),"id = $photo_id");
        }

        $completeness = Application_Model_Common::UpdateCompleteness($id);
        $this->update(array(
            'experience' => $experience,
            'completeness' => $completeness,
            'photo' => $photo
        ),"id = $id");

        return $id;
    }

    public function facebookLogin($token)
    {
        $user = $this->getUser($token,false,'facebook_key',true,true);
        if ($user != null) {
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            $facebook = new Application_Model_Facebook();
            $user_profile = $facebook->getUser($token);
            if ($user_profile) {
                $user = $this->getUser($user_profile['email'],false,'email',true,true);
                if ($user) {
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
        $user = $this->getUser($token,false,'linkedin_key',true,true);
        if ($user != null) {
            return array(
                'body' => $user,
                'errorCode' => '200'
            );
        } else {
            $linkedin = new Application_Model_Linkedin();
            $user_profile = $linkedin->getUser($token);
            if ($user_profile) {
                $user = $this->getUser($user_profile['email'],false,'email',true,true);
                if ($user) {
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
                        'body' => $user_profile,
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

    public function prepeareUser($res, $user = false) {

        //Prepare Languages,Skills
        if ($res['skills'] != null && $res['skills'] != '') {
            $res['skills'] = explode(',',$res['skills']);
        }
        else {
            $res['skills'] = array();
        }
        if ($res['languages'] != null && $res['languages'] != '') {
            $res['languages'] = explode(',',$res['languages']);
        }
        else {
            $res['languages'] = array();
        }

        $jobs = new Application_Model_DbTable_UserJobs();

        //Get user education
        $res['education'] = $jobs->getEducation($res['id']);

        //Get UserJobs
        $res['jobs'] = $jobs->getJobs($res['id']);

        // Add photo
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;
        if ($res['photo'] != '' && $res['photo'] != null) {
            $res['photo'] = $url.$res['photo'];
        }
        else {
            $res['photo'] = null;
        }

        // Public?Private request
        if ($user) {
            $user_id = $user['id'];
            $friend_id = $res['id'];

            // Friends ?
            $friends = $this->_db->fetchOne("
                select id
                from user_friends
                where user_id = $user_id
                and status = 1
                and friend_id = $friend_id
                limit 1
            ");
            if ($friends) {
                $res['friend'] = true;
            }
            else {
                unset($res['email']);
                unset($res['business_email']);
                unset($res['skype']);
                unset($res['phone']);
                $res['friend'] = false;
            }

            // Meet ?
            $meet = $this->_db->fetchOne("
                select id
                from calendar
                where ( (user_id = $user_id and user_id_second = $friend_id) or (user_id = $friend_id and user_id_second = $user_id) )
                and `type` = 2
                and `status` = 2
                limit 1
            ");
            if ($meet) {
                $res['meet'] = true;
            }
            else {
                $res['meet'] = false;
            }
        }
        else {
            // Add user settings
            $settings = new Application_Model_DbTable_UserSettings();
            $user_settings = $settings->getSettings($res);
            $res['settings'] = $user_settings;
        }

        return $res;
    }

    public function getUser($id,$user,$by = 'id',$prepeare = true, $private = false) {
        if ($private) {
            $private = ', u.private_key';
        }
        else {
            $private = '';
        }
        $res = $this->_db->fetchRow("
            select u.id $private,u.name,u.lastname,u.email,u.industry_id,u.summary,u.photo,u.phone,u.business_email,u.skype,u.rating,u.experience, u.completeness,u.contacts,u.meet_succesfull,u.meet_succesfull, group_concat(DISTINCT s.name) as skills, group_concat(DISTINCT l.languages_id) as languages
            from users u
            left join user_skills s on u.id = s.user_id
            left join user_languages l on u.id = l.user_id
            where
            u.$by = '$id'
        ");

        if (is_numeric($res['id'])) {
            // Prepeare User
            if ($prepeare) {
                $res = $this->prepeareUser($res, $user);
            }
            return $res;
        }
        else
            return false;
    }

    public static function getUserData($private_key) {
        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select *
            from users
            where private_key = '$private_key'
        ");
        if ($res != null) {
            return $res;
        }
        else {
            return false;
        }
    }
}

