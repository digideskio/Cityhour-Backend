<?php

class Application_Model_DbTable_UserContactsWait extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_contacts_wait';

    public function add($data) {
        $this->insert($data);
        return true;
    }

    public function userUpdateInfo($id,$user,$token,$type) {
        if ($type == 1) {
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
            $res = $linkedin->getFriends($token);

            if (isset($res) && $res != null) {
                return $res;
            }
            else {
                return array();
            }
        }

        $id = $user['id'];
        if ($type == 1) {
            if ($token && $token != null && $token != '') {
                $facebook = new Application_Model_Facebook();
                $facebook->storeInfo($token,$id);
                $res = $this->_db->fetchAll("
                    select u.id, w.name, w.lastname, w.photo, CASE
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
                        and n.status in (0,1)
                        ) > 0 then 1
                        else 0
                      END as status
                    from users u
                    left join user_contacts_wait w on u.facebook_id = w.linkedin_id
                    where
                      user_id = $id and w.type = 1
                ");
            }
            else {
                return array();
            }
        }
        else if ($type == 2) {
            $token =  $user['linkedin_key'];
            $linkedin = new Application_Model_Linkedin();
            $linkedin->storeInfo($token,$id);
            $res = $this->_db->fetchAll("
                select u.id, w.name, w.lastname, w.photo, CASE
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
                  	and n.status in (0,1)
                  	) > 0 then 1
                 	else 0
                  END as status
                from users u
                left join user_contacts_wait w on u.linkedin_id = w.linkedin_id
                where
                  user_id = $id and w.type = 2
            ");
        }
        else if ($type == 3) {
            $token = @json_decode($token,true);
            $emails = "'".implode("','",$token['emails'])."'";
            $phones = "'".implode("','",$token['phones'])."'";
            if (!$emails) $emails = 0;
            if (!$phones) $phones = 0;
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->userPhoto->url;

            $res = $this->_db->fetchAll("
                  select distinct(u.id), u.name, u.lastname, concat('$url',u.photo) as photo,
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
                  	and n.status in (0,1)
                  	) > 0 then 1
                 	else 0
                  END as status
                  from users u
                  where
                  ( u.phone in ($phones) or u.email in ($emails) or u.business_email in ($emails) )
                  and u.id != $id
            ");
        }

        if (isset($res) && $res != null) {
            return $res;
        }
        else {
            return array();
        }
    }

}