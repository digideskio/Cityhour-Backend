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

    public function blocked($private_key) {
        $filter = new Zend_Filter_Alnum();
        $private_key = $filter->filter($private_key);
        $res = $this->_db->fetchRow("
            select u.id,u.status,s.value as reason,s2.value as hours
            from users u
            left join user_settings s on u.id = s.user_id and s.name = 'blocked'
            left join user_settings s2 on u.id = s2.user_id and s2.name = 'hours'
            where private_key = '$private_key'
        ");
        if (!isset($res['id']) || !is_numeric($res['id'])) {
            return 404;
        }
        if ((int)$res['status'] === 0) {
            return array(
                'status' => false
            );
        }
        else {
            return array(
                'status' => true,
                'reason' => $res['reason'],
                'hours' => $res['hours'],
            );
        }
    }

    public function getLinkedinUsers($users_in) {
        return $this->_db->fetchAll("
            select id, linkedin_id
            from users
            where linkedin_id in ($users_in)
            group by id
        ");
    }

    public function updateUser($user,$data) {
        $user_id = $user['id'];

        $userData = array();
        $validators = array(
            '*' => array()
        );
        $filter_alnum = new Zend_Filter_Alnum(true);
        $filters = array(
            'name' => array('StringTrim','HtmlEntities',$filter_alnum),
            'lastname' => array('StringTrim','HtmlEntities',$filter_alnum),
            'summary' => array('StringTrim','HtmlEntities'),
            'skype' => array('StringTrim','HtmlEntities'),
            'phone' => array('StringTrim','HtmlEntities'),
            'industry_id' => array('StringTrim','HtmlEntities','Int'),
            'business_email' => array('StringTrim','HtmlEntities'),
            'city' => array('StringTrim','HtmlEntities'),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        $this->_db->beginTransaction();
        try {
            if (isset($data['name'])) $userData['name'] = $input->getEscaped('name');
            if (isset($data['lastname'])) $userData['lastname'] = $input->getEscaped('lastname');
            if (isset($data['summary'])) $userData['summary'] = $data['summary'];
            if (isset($data['skype'])) $userData['skype'] = $input->getEscaped('skype');
            if (isset($data['phone'])) $userData['phone'] = $input->getEscaped('phone');
            if (isset($data['industry_id'])) $userData['industry_id'] = $input->getEscaped('industry_id');
            if (isset($data['business_email'])) $userData['business_email'] = $input->getEscaped('business_email');


            //City
            if ($input->getEscaped('city')) {
                $city = array_merge($userData,Application_Model_Common::getCity($input->getEscaped('city')));
                $userData['city'] = $city['city'];
                $userData['city_name'] = $city['city_name'];
            }

            //Jobs
            if (isset($data['jobs'])) {
                $validators = array(
                    '*' => array()
                );
                $filters = array(
                    'current' => array('StringTrim','HtmlEntities','Int'),
                    'start_time' => array('StringTrim','HtmlEntities'),
                    'end_time' => array('StringTrim','HtmlEntities'),
                );

                foreach($data['jobs'] as $num=>$row) {
                    $jobs_input = new Zend_Filter_Input($filters, $validators, $row);

                    if (!$row['current'] || $row['current'] === false ) {
                        $row['current'] = 0;
                    }
                    elseif ($row['current'] === true) {
                        $row['current'] = 1;
                    }
                    if (!isset($row['end_time'])) {
                        if (isset($row['start_time'])) {
                            $row['end_time'] = $row['start_time'];
                        }
                        else {
                            $row['end_time'] = null;
                        }
                    }
                    if (isset($row['id']) && is_numeric($row['id'])) {
                        $job_id = $row['id'];
                        $this->_db->update('user_jobs',array(
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'current' => $jobs_input->getEscaped('current'),
                            'start_time' => $jobs_input->getEscaped('start_time'),
                            'end_time' => $jobs_input->getEscaped('end_time')
                        ),"id = $job_id");
                    }
                    else {
                        $this->_db->insert('user_jobs',array(
                            'user_id' => $user_id,
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'current' => $jobs_input->getEscaped('current'),
                            'start_time' => $jobs_input->getEscaped('start_time'),
                            'end_time' => $jobs_input->getEscaped('end_time'),
                            'type' => 0
                        ));
                    }
                }
                $userData['experience'] = Application_Model_Common::UpdateExperience($user_id);
            }


            //Education
            if (isset($data['education'])) {
                $validators = array(
                    '*' => array()
                );
                $filters = array(
                    'current' => array('StringTrim','HtmlEntities','Int'),
                    'start_time' => array('StringTrim','HtmlEntities'),
                    'end_time' => array('StringTrim','HtmlEntities'),
                );

                foreach($data['education'] as $num=>$row) {
                    $jobs_input = new Zend_Filter_Input($filters, $validators, $row);

                    if (isset($row['id']) && is_numeric($row['id'])) {
                        $job_id = $row['id'];
                        $this->_db->update('user_jobs',array(
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'start_time' => $jobs_input->getEscaped('start_time'),
                            'end_time' => $jobs_input->getEscaped('end_time')
                        ),"id = $job_id");
                    }
                    else {
                        $this->_db->insert('user_jobs',array(
                            'user_id' => $user_id,
                            'name' => $row['name'],
                            'company' => $row['company'],
                            'start_time' => $jobs_input->getEscaped('start_time'),
                            'end_time' => $jobs_input->getEscaped('end_time'),
                            'type' => 1
                        ));
                    }
                }
            }

            //Skills
            if (isset($data['skills'])) {
                $this->_db->delete('user_skills',"user_id = $user_id");
                if (isset($data['skills'][0]) && $data['skills'][0]) {
                    foreach($data['skills'] as $num=>$row) {
                        $this->_db->insert('user_skills',array(
                            'user_id' => $user_id,
                            'name' => $filter_alnum->filter($row),
                        ));
                    }
                }
            }

            //Languages
            if (isset($data['languages'])) {
                $this->_db->delete('user_languages',"user_id = $user_id");
                if (isset($data['languages'][0]) && $data['languages'][0]) {
                    foreach($data['languages'] as $num=>$row) {
                        $this->_db->insert('user_languages',array(
                            'user_id' => $user_id,
                            'languages_id' => $filter_alnum->filter($row),
                        ));
                    }
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

            return $this->getUser($user_id,$user);
        } catch (Exception $e){
            $this->_db->rollBack();
            return false;
        }
    }


    public function prepeareUsers($res, $user, $full = false) {
        $answer = array();


        if (!$full) {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->userPhoto->url;

            $answer = $this->_db->fetchAll("
                select distinct(u.id),u.name,u.lastname,u.industry_id, concat ('$url', u.photo) as photo,u.phone,u.business_email,u.skype,u.rating,u.city_name,j.name as job_name, j.company as job_company
                from users u
                left join user_jobs j on u.id = j.user_id
                where
                j.type = 0 and j.current = 1
                and u.status = 0
                and u.id in ($res)
            ");
        }
        else {
            $res = explode(',', $res);
            foreach ($res as $num=>$row) {
                if (is_numeric($row)) {
                    $answer[$num] = $this->getUser($row,$user);
                }
            }
        }

        return $answer;
    }

    public function registerUser($data) {
        $validators = array(
            '*' => array(),
            'business_email' => new Zend_Validate_EmailAddress(),
            'email' => new Zend_Validate_EmailAddress(),
        );
        $filter_alnum = new Zend_Filter_Alnum(true);
        $filters = array(
            'name' => array('StringTrim','HtmlEntities',$filter_alnum),
            'lastname' => array('StringTrim','HtmlEntities',$filter_alnum),
            'country' => array('StringTrim','HtmlEntities',$filter_alnum),
            'summary' => array('StringTrim','HtmlEntities'),
            'skype' => array('StringTrim','HtmlEntities'),
            'phone' => array('StringTrim','HtmlEntities'),
            'industry_id' => array('StringTrim','HtmlEntities','Int'),
            'offset' => array('StringTrim','HtmlEntities','Int'),
            'city' => array('StringTrim','HtmlEntities'),
            'facebook_key' => array('StringTrim','HtmlEntities'),
            'facebook_id' => array('StringTrim','HtmlEntities'),
            'linkedin_key' => array('StringTrim','HtmlEntities'),
            'linkedin_id' => array('StringTrim','HtmlEntities'),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        $this->_db->beginTransaction();

        try {
            //Important
            if ($input->getEscaped('email')) $userData['email'] = $input->getEscaped('email');
            if ($input->getEscaped('name')) $userData['name'] = $input->getEscaped('name');
            if ($input->getEscaped('lastname')) $userData['lastname'] = $input->getEscaped('lastname');
            if ($input->getEscaped('industry_id')) $userData['industry_id'] = $input->getEscaped('industry_id');
            $userData['private_key'] = uniqid(sha1(time()), false);

            //Not Important
            if ($input->getEscaped('summary')) $userData['summary'] = $data['summary'];
            if ($input->getEscaped('country')) $userData['country'] = $data['country'];
            if ($input->getEscaped('skype')) $userData['skype'] = $input->getEscaped('skype');
            if ($input->getEscaped('phone')) $userData['phone'] = $input->getEscaped('phone');
            if ($input->getEscaped('business_email')) $userData['business_email'] = $input->getEscaped('business_email');
            if ($input->getEscaped('facebook_key')) $userData['facebook_key'] = $input->getEscaped('facebook_key');
            if ($input->getEscaped('facebook_id')) $userData['facebook_id'] = $input->getEscaped('facebook_id');
            if ($input->getEscaped('linkedin_key')) $userData['linkedin_key'] = $input->getEscaped('linkedin_key');
            if ($input->getEscaped('linkedin_id')) $userData['linkedin_id'] = $input->getEscaped('linkedin_id');


            if ($input->getEscaped('city')) {
                $city = array_merge($userData,Application_Model_Common::getCity($input->getEscaped('city')));
                $userData['city'] = $city['city'];
                $userData['city_name'] = $city['city_name'];
                $userData['free_city'] = $city['city'];
                $userData['free_city_name'] = $city['city_name'];
                $userData['free_lat'] = $city['lat'];
                $userData['free_lng'] = $city['lng'];
            }

            $id = $this->insert($userData);


            if (isset($data['skills'][0]) && $data['skills'][0]  && $data['skills'][0] != '') {
                foreach($data['skills'] as $num=>$row) {
                    $this->_db->insert('user_skills',array(
                        'user_id' => $id,
                        'name' => $filter_alnum->filter($row),
                    ));
                }
            }

            if (isset($data['languages'][0]) && $data['languages'][0]  && $data['languages'][0] != '') {
                foreach($data['languages'] as $num=>$row) {
                    $this->_db->insert('user_languages',array(
                        'user_id' => $id,
                        'languages_id' => $filter_alnum->filter($row),
                    ));
                }
            }

            if (isset($data['jobs'][0])) {
                $validators = array(
                    '*' => array()
                );
                $filters = array(
                    'current' => array('StringTrim','HtmlEntities','Int'),
                    'start_time' => array('StringTrim','HtmlEntities'),
                    'end_time' => array('StringTrim','HtmlEntities'),
                );
                foreach($data['jobs'] as $num=>$row) {
                    $jobs_input = new Zend_Filter_Input($filters, $validators, $row);

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
                        'current' => $jobs_input->getEscaped('current'),
                        'start_time' => $jobs_input->getEscaped('start_time'),
                        'end_time' => $jobs_input->getEscaped('end_time'),
                        'type' => 0
                    ));
                }
            }

            if (isset($data['education'][0])) {
                $validators = array(
                    '*' => array()
                );
                $filters = array(
                    'current' => array('StringTrim','HtmlEntities','Int'),
                    'start_time' => array('StringTrim','HtmlEntities'),
                    'end_time' => array('StringTrim','HtmlEntities'),
                );
                foreach($data['education'] as $num=>$row) {
                    $jobs_input = new Zend_Filter_Input($filters, $validators, $row);
                    $this->_db->insert('user_jobs',array(
                        'user_id' => $id,
                        'name' => $row['name'],
                        'company' => $row['company'],
                        'current' => 0,
                        'start_time' => $jobs_input->getEscaped('start_time'),
                        'end_time' => $jobs_input->getEscaped('end_time'),
                        'type' => 1
                    ));
                }
            }


            $photo = null;
            if (isset($data['photo_id']) && is_numeric($data['photo_id'])) {
                $photo_id = $data['photo_id'];
                $photo = $this->_db->fetchOne("
                    select circle_260
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
                'name' => 'offset',
                'value' => (int)$input->getEscaped('offset')
            ));

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
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'goal',
                'value' => 1
            ));
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'newMessageSync',
                'value' => 1
            ));
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'incomingMeetingInviteSync',
                'value' => 1
            ));
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'nativeCalendarSync',
                'value' => 1
            ));
            $this->_db->insert('user_settings',array(
                'user_id' => $id,
                'name' => 'contactRequestSync',
                'value' => 1
            ));

            if ($userData['linkedin_id']) {
                (new Application_Model_DbTable_UserContactsWait())->linkedinFriendsNotify($id,$userData);
            }

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
        if ($user) {
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
        if ($user) {
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
            $res['skills'] = explode('$$$$$',$res['skills']);
        }
        else {
            $res['skills'] = array();
        }
        if ($res['languages'] != null && $res['languages'] != '') {
            $res['languages'] = explode('$$$$$',$res['languages']);
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

        //Blocked
        if ($res['status'] != 0) {
            $res['blocked'] = true;
        }
        else {
            $res['blocked'] = false;
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
                $res['lastname'] = mb_substr($res['lastname'], 0, 1, 'UTF-8').'.';
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

            // Request ?
            $meet = $this->_db->fetchOne("
                select id
                from calendar
                where ( (user_id = $user_id and user_id_second = $friend_id) or (user_id = $friend_id and user_id_second = $user_id) )
                and `type` = 2
                and `status` = 1
                limit 1
            ");
            if ($meet) {
                $res['request'] = true;
            }
            else {
                $res['request'] = false;
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
        $this->_db->quote($id);
        $res = $this->_db->fetchRow("
            select u.id $private, u.country, u.name, u.lastname, u.status, u.email,u.city_name,u.city,u.industry_id,u.summary,u.photo,u.phone,u.business_email,u.skype,u.rating,u.experience, u.completeness,u.contacts,u.meet_succesfull,u.meet_declined, group_concat(DISTINCT s.name SEPARATOR '$$$$$') as skills, group_concat(DISTINCT l.languages_id SEPARATOR '$$$$$') as languages
            from users u
            left join user_skills s on u.id = s.user_id
            left join user_languages l on u.id = l.user_id
            where
            u.$by = '$id'
        ");

        if (isset($res['id'])) {
            // Prepeare User
            if ($prepeare) {
                $res = $this->prepeareUser($res, $user);
            }
            return $res;
        }
        else
            return false;
    }

    public static function getUserData($private_key,$block = false) {
        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select *
            from users
            where private_key = '$private_key'
        ");
        if ($res != null && $res['status'] == 0) {
            return $res;
        }
        elseif ($res != null && $res['status'] == 1 && $block) {
            return $res;
        }
        else {
            return false;
        }
    }

    public static function authorize($private_key,$block = true) {
        if ($private_key) {
            $filter = new Zend_Filter_Alnum();
            $private_key = $filter->filter($private_key);
            $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select *
            from users
            where private_key = '$private_key'
        ");
            if ($res && $res['status'] == 0) {
                return $res;
            }
            elseif ($res && $res['status'] == 1 && $block) {
                Zend_Controller_Action_HelperBroker::getStaticHelper('json')->sendJson(array(
                    'errorCode' => '407'
                ));
            }
            elseif ($res && $res['status'] == 1 && !$block) {
                return $res;
            }
            else {
                Zend_Controller_Action_HelperBroker::getStaticHelper('json')->sendJson(array(
                    'errorCode' => '401'
                ));
            }
        }
        Zend_Controller_Action_HelperBroker::getStaticHelper('json')->sendJson(array(
            'errorCode' => '401'
        ));
    }

    public static function isValidUser($id) {
        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select id,`status`
            from users
            where id = $id
        ");
        if ($res && $res['status'] == 0) {
            return true;
        }
        else {
            return false;
        }
    }
}

