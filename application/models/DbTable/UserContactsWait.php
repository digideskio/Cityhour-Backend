<?php

class Application_Model_DbTable_UserContactsWait extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_contacts_wait';

    public function add($data) {
        $this->insert($data);
        return true;
    }

    public function getByUser($id,$type = 2) {
        return $this->_db->fetchCol("
            select linkedin_id
            from $this->_name
            where user_id = $id and `type` = $type
        ");
    }

    public function userUpdateInfo($id,$user,$token,$type,$user_real) {
        if ($type == 1) {
            if (!$user_real['facebook_key']) {
                $this->facebookFriendsNotify($user_real['id'],$user_real,$user);
            }
            $this->_db->update('users',array(
                'facebook_id' => $user,
                'facebook_key' => $token
            ),"id = $id");
        }
        elseif ($type == 2) {
            $this->_db->update('users',array(
                'linkedin_id' => $user,
                'linkedin_key' => $token
            ),"id = $id");
        }
        return true;
    }

    public function linkedinFriendsNotify($id,$user) {
        $wid = $user['linkedin_id'];
        $res = $this->_db->fetchAll("
                select w.user_id as id
                from user_contacts_wait w
                left join users u on w.user_id = u.id
                where
                w.linkedin_id = '$wid'
                and w.type = 2
                and u.status = 0
            ");
        if ($res) {
            foreach ($res as $num=>$row) {
                $this->_db->insert('notifications',array(
                    'from' => $id,
                    'to' => $row['id'],
                    'item' => $id,
                    'type' => 7,
                    'text' => Application_Model_Texts::notification()[7],
                    'template' => 0,
                    'action' => 6
                ));

                $lastname = trim($user['lastname'])[0].'.';
                $fullName = $user['name'].' '.$lastname;
                $text = Application_Model_Texts::push($fullName)[9];
                (new Application_Model_DbTable_Push())->sendPush($row['id'],$text,9,array(
                    'from' => $id,
                    'type' => 9,
                    'item' => $id,
                    'action' => 6
                ));
            }
        }

        return true;
    }

    public function facebookFriendsNotify($id,$user,$wid) {
        $res = $this->_db->fetchAll("
            select w.user_id as id
            from user_contacts_wait w
            left join users u on w.user_id = u.id
            where
            w.linkedin_id = '$wid'
            and w.type = 1
            and u.status = 0
        ");
        if ($res) {
            foreach ($res as $num=>$row) {
                $this->_db->insert('notifications',array(
                    'from' => $id,
                    'to' => $row['id'],
                    'item' => $id,
                    'type' => 8,
                    'text' => Application_Model_Texts::notification()[8],
                    'template' => 0,
                    'action' => 6
                ));

                $lastname = trim($user['lastname'])[0].'.';
                $fullName = $user['name'].' '.$lastname;
                $text = Application_Model_Texts::push($fullName)[10];
                (new Application_Model_DbTable_Push())->sendPush($row['id'],$text,10,array(
                    'from' => $id,
                    'type' => 10,
                    'item' => $id,
                    'action' => 6
                ));
            }
        }

        return true;
    }

    public function updateFacebookData($data,$id, $user_id) {
        $this->update($data,"linkedin_id = '$id' and user_id = $user_id");
        return true;
    }

    public function updateLinkedinData($data,$id, $user_id) {
        $this->update($data,"linkedin_id = '$id' and user_id = $user_id");
        return true;
    }

    public function getAll($user, $type, $token) {

        // If registration using linkedin
        if ($type == 4 && $user == false) {
            $linkedin = new Application_Model_Linkedin();
            $res = $linkedin->getFriends($token,true);

            if (isset($res) && $res != null) {
                return $res;
            }
            else {
                return array();
            }
        }

        // Facebook
        $id = $user['id'];
        if ($type == 1) {
            if ($token) {
                $facebook = new Application_Model_Facebook();
                $facebook->storeInfo($token,$id,$user);

                $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
                $url = $config->userPhoto->url;

                $res = $this->_db->fetchAll("
                    select u.id, concat('$url', u.photo) AS photo, w.name, w.lastname, j.name as job, j.company, CASE
                        when ( select distinct(f.id)
                        from user_friends f
                        where f.user_id = $id
                        and f.friend_id = u.id
                        and f.status = 1
                        ) > 0 then 2
                        when ( select distinct(n.id)
                        from notifications n
                        where n.from = $id
                        and n.to = u.id
                        and n.type = 0
                        and n.status = 0
                        ) > 0 then 1
                        else 0
                      END as status
                    from users u
                    left join user_contacts_wait w on u.facebook_id = w.linkedin_id
                    LEFT JOIN user_jobs j ON u.id = j.user_id AND j.current = 1 AND j.type = 0
                    where
                      w.user_id = $id and w.type = 1
                    group by u.id
                    having status != 2
                ");
            }
            else {
                return array();
            }
        }

        // Linkedin
        else if ($type == 2) {
            $token =  $user['linkedin_key'];
            $linkedin = new Application_Model_Linkedin();
            $linkedin->storeInfo($token,$id);

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->userPhoto->url;

            $res = $this->_db->fetchAll("
                select u.id, concat('$url', u.photo) AS photo, w.name, w.lastname, j.name as job, j.company, CASE
                  	when ( select distinct(f.id)
                  	from user_friends f
                  	where f.user_id = $id
                  	and f.friend_id = u.id
                  	and f.status = 1
                  	limit 1
                  	) > 0 then 2
                  	when ( select distinct(n.id)
                  	from notifications n
                  	where n.from = $id
                  	and n.to = u.id
                  	and n.type = 0
                  	and n.status = 0
                  	limit 1
                  	) > 0 then 1
                 	else 0
                  END as status
                from users u
                left join user_contacts_wait w on u.linkedin_id = w.linkedin_id
                LEFT JOIN user_jobs j ON u.id = j.user_id AND j.current = 1 AND j.type = 0
                where
                  w.user_id = $id and w.type = 2
                group by u.id
                having status != 2
            ");
        }

        //Address book
        else if ($type == 3) {
            $emails = array();
            $phones = array();
            $filter_int = new Zend_Filter_Digits();
            $valid_email = new Zend_Validate_EmailAddress();

            if (isset($token['phones'])) {
                foreach ($token['phones'] as $num => $row) {
                    $phone = $filter_int->filter($row);
                    if (is_numeric($phone)) {
                        $phones[$num] = $phone;
                    }
                }
                if ($phones) {
                    $phones = "u.phone like '%".implode("%' or u.phone like '%",$phones)."%'";
                }
                else {
                    $phones = 'u.id = 0';
                }
            }
            else {
                $phones = 'u.id = 0';
            }

            if (isset($token['emails'])) {
                foreach ($token['emails'] as $num => $row) {
                    if ($valid_email->isValid($row)) {
                        $emails[$num] = $row;
                    }
                }

                if ($emails) {
                    $business_emails = "or u.business_email like '%".implode("%' or u.business_email like '%",$emails)."%'";
                    $emails = "or u.email like '%".implode("%' or u.email like '%",$emails)."%'";
                }
                else {
                    $business_emails = '';
                    $emails = '';
                }
            }
            else {
                $emails = '';
                $business_emails = '';
            }

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->userPhoto->url;

            if ($phones || $emails || $business_emails) {
                $res = $this->_db->fetchAll("
                  select distinct(u.id), u.name, u.lastname, concat('$url', u.photo) AS photo, j.name as job, j.company,
                  CASE
                  	when ( select distinct(f.id)
                  	from user_friends f
                  	where f.user_id = $id
                  	and f.friend_id = u.id
                  	and f.status = 1
                  	) > 0 then 2
                  	when ( select distinct(n.id)
                  	from notifications n
                  	where n.from = $id
                  	and n.to = u.id
                  	and n.type = 0
                  	and n.status = 0
                  	) > 0 then 1
                 	else 0
                  END as status
                  from users u
                  LEFT JOIN user_jobs j ON u.id = j.user_id AND j.current = 1 AND j.type = 0
                  where
                  ( $phones  $emails  $business_emails )
                  and u.id != $id
                  group by u.id
                  having status != 2
            ");
            }
        }

        // Email
        else if ($type == 5) {
            $validator = new Zend_Validate_EmailAddress();
            if (isset($token['emails'][0]) && isset($token['name']) && $validator->isValid($token['emails'][0])) {

                $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
                $url = $config->userPhoto->url;
                $name = $user['name'].' '.$user['lastname'];
                $options = array(
                    'name' => $name,
                    'photo' => $url.$user['photo']
                );
                Application_Model_Common::sendEmail($token['emails'][0], $name." invited you to download an app", null, null, null, "invite_email.phtml", $options, 'invite');

                $res = true;
            }
            else {
                $res = false;
            }
        }

        if (isset($res) && $res != null) {
            return $res;
        }
        else {
            return array();
        }
    }

}