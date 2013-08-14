<?php

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';
    private $paginatorNumberPerPage = 50;

    public function getList($data = false) {
        if ($data) {
            $email = $data['search'];
            $rows =  $this->fetchAll("email like \"%$email%\" ",'id desc')->toArray();
        }
        else {
            $rows =  $this->fetchAll(null,'id desc')->toArray();
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setItemCountPerPage($this->paginatorNumberPerPage);
        $cur_page = isset($data['page']) ? $data['page'] : 0;
        $paginator->setCurrentPageNumber($cur_page);
        return $paginator;
    }

    public function saveUser($data,$id) {
        $this->update($data,"id = $id");
        return true;
    }

    public function updateUser($user,$data) {
        $user_id = $user['id'];
        $filter = new Zend_Filter_StripTags();
        $userData = array();
        $this->_db->beginTransaction();
        try {
            if (isset($data['name'])) $userData['name'] = $filter->filter($data['name']);
            if (isset($data['lastname'])) $userData['lastname'] = $filter->filter($data['lastname']);
            if (isset($data['industry_id'])) $userData['industry_id'] = $filter->filter($data['industry_id']);
            if (isset($data['summary'])) $userData['summary'] = $filter->filter($data['summary']);
            if (isset($data['skype'])) $userData['skype'] = $filter->filter($data['skype']);
            if (isset($data['summary'])) $userData['summary'] = $filter->filter($data['summary']);
            if (isset($data['phone'])) $userData['phone'] = $filter->filter($data['phone']);
            if (isset($data['business_email'])) $userData['business_email'] = $filter->filter($data['business_email']);


            //Jobs
            if (isset($data['jobs'])) {
                foreach($data['jobs'] as $num=>$row) {
                    if (!$row['current'] || $row['current'] === false ) {
                        $row['current'] = 0;
                    }
                    elseif ($row['current'] === true) {
                        $row['current'] = 1;
                    }
                    if (!isset($row['end_time'])) {
                        $row['end_time'] = $row['start_time'];
                    }
                    if (isset($row['id'])) {
                        $job_id = $row['id'];
                        $this->_db->update('user_jobs',array(
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'current' => $row['current'],
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time']
                        ),"id = $job_id");
                    }
                    else {
                        $this->_db->insert('user_jobs',array(
                            'user_id' => $user_id,
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'current' => $row['current'],
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time'],
                            'type' => 0
                        ));
                    }
                }
                $userData['experience'] = Application_Model_Common::UpdateExperience($user_id);
            }


            //Education
            if (isset($data['education'])) {
                foreach($data['education'] as $num=>$row) {
                    if (isset($row['id'])) {
                        $job_id = $row['id'];
                        $this->_db->update('user_jobs',array(
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time']
                        ),"id = $job_id");
                    }
                    else {
                        $this->_db->insert('user_jobs',array(
                            'user_id' => $user_id,
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time'],
                            'type' => 1
                        ));
                    }
                }
            }

            //City
            if (isset($data['city'])) {
                $userData = array_merge($userData,Application_Model_Common::getCity($data['city']));
            }

            //Skills
            if (isset($data['skills'])) {
                $this->_db->delete('user_skills',"user_id = $user_id");
                foreach($data['skills'] as $num=>$row) {
                    $this->_db->insert('user_skills',array(
                        'user_id' => $user_id,
                        'name' => $row,
                    ));
                }
            }

            //Languages
            if (isset($data['languages'])) {
                $this->_db->delete('user_languages',"user_id = $user_id");
                foreach($data['languages'] as $num=>$row) {
                    $this->_db->insert('user_languages',array(
                        'user_id' => $user_id,
                        'languages_id' => $row,
                    ));
                }
            }

            if ($userData) {
                $this->update($userData,"id = $user_id");
            }

            $completeness = Application_Model_Common::UpdateCompleteness($user_id);
            $this->update(array(
                'completeness' => $completeness
            ),"id = $user_id");
            $this->_db->commit();
            return true;
        } catch (Exception $e){
            $this->_db->rollBack();
            return $e->getMessage();
        }
    }


    public function prepeareUsers($res, $user, $full = false) {
        $answer = array();
        if (!$full) {
            $answer = $this->_db->fetchAll("
                select distinct(u.id),u.name,u.lastname,u.industry_id,u.photo,u.phone,u.business_email,u.skype,u.rating,u.city_name,j.name as job_name, j.company as job_company
                from users u
                left join user_jobs j on u.id = j.user_id
                where
                j.type = 0 and j.current = 1
                and u.id in ($res)
            ");
        }
        else {
            $res = explode(',', $res);
            foreach ($res as $num=>$row) {
                $answer[$num] = $this->getUser($row,$user);
            }
        }

        return $answer;
    }

    public function getPeople($user,$data_from,$data_to,$city,$industry,$goals,$after) {
        $user_id = $user['id'];



        $res = $this->_db->fetchAll("
            select distinct(u.id) as user_id, c.id as event_id, u.name,u.lastname, j.name as job_name, j.company, u.industry_id, u.city_name, u.rating, c.goal, c.foursquare_id, c.place, c.lat, c.lng,
            CASE
                when ( select distinct(f.id)
                from user_friends f
                where f.user_id = 58
                and f.friend_id = u.id
                and f.status = 1
            ) > 0 then 1
            else 0
            END as friend,
            CASE
                when ( select distinct(m.id)
                from calendar m
                where
                m.type = 2
                and ((m.user_id = 58 and m.user_id_second = u.id) or (m.user_id = u.id and m.user_id_second = 58))
            ) > 0 then 1
            else 0
            END as meet

            from calendar c
            left join users u on c.user_id = u.id
            left join user_jobs j on u.id = j.user_id

            where
            c.user_id != 1
            -- time
            and j.type = 0 and j.current = 1
            and c.city = 'string'

            and u.industry_id = 1
            and c.goal = 1

            order by c.start_time
            LIMIT 0,25
        ");
        if ($res != null) {

            return $res;
        }
        else {
            return array();
        }

    }

    public function registerUser($data) {
        $filter = new Zend_Filter_StripTags();
        $this->_db->beginTransaction();
        try {
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


            if (isset($data['skills'][0]) && $data['skills'][0]  && $data['skills'][0] != '') {
                foreach($data['skills'] as $num=>$row) {
                    $this->_db->insert('user_skills',array(
                        'user_id' => $id,
                        'name' => $row,
                    ));
                }
            }

            if (isset($data['languages'][0]) && $data['languages'][0]  && $data['languages'][0] != '') {
                foreach($data['languages'] as $num=>$row) {
                    $this->_db->insert('user_languages',array(
                        'user_id' => $id,
                        'languages_id' => $row,
                    ));
                }
            }


            foreach($data['jobs'] as $num=>$row) {
                if (!$row['current'] || $row['current'] === false ) {
                    $row['current'] = 0;
                }
                elseif ($row['current'] === true) {
                    $row['current'] = 1;
                }
                if (!isset($row['end_time'])) {
                    $row['end_time'] = $row['start_time'];
                }
                $this->_db->insert('user_jobs',array(
                    'user_id' => $id,
                    'name' => $row['name'],
                    'company' => $row['company'],
                    'current' => $row['current'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'type' => 0
                ));
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

            //Add default settings
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'city',
                'value' => $userData['city']
            ));
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'free_time',
                'value' => 1
            ));

            $completeness = Application_Model_Common::UpdateCompleteness($id);
            $experience = Application_Model_Common::UpdateExperience($id);
            $this->update(array(
                'experience' => $experience,
                'completeness' => $completeness,
                'photo' => $photo
            ),"id = $id");
            $this->_db->commit();
        } catch (Exception $e){
            $this->_db->rollBack();
            return $e->getMessage();
        }
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
                        'linkedin_id' => $user_profile['linkedin_id']
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
        if (isset($user['id']) && $id == $user['id']) {
            $user = false;
            $private = true;
        }

        if ($private) {
            $private = ', u.private_key, u.facebook_key, u.facebook_id, u.linkedin_key, u.linkedin_id, u.status';
        }
        else {
            $private = '';
        }
        $res = $this->_db->fetchRow("
            select u.id $private,u.name,u.lastname,u.email,u.city_name,u.industry_id,u.summary,u.photo,u.phone,u.business_email,u.skype,u.rating,u.experience, u.completeness,u.contacts,u.meet_succesfull,u.meet_declined, group_concat(DISTINCT s.name) as skills, group_concat(DISTINCT l.languages_id) as languages
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

