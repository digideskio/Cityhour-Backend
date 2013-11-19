<?php

require 'RDS.class.php';

class Common {

    /** @var array $config Config */
    var $config;

    /** @var object DB class */
    var $db;

    /** @var boolean $debug Debug true/false */
    var $debug;

    /** @var array $data Income paramms */
    var $data;

    /** @var int $start When script start working */
    var $start;

    /** @var int $free Array of user free time intervals */
    var $free;

    /** @var int $b_s Bussines time start */
    /** @var int $b_e Bussines time end */
    var $b_s;
    var $b_e;

    /** @var int $s_full Full time start */
    /** @var int $e_full Full time end */
    var $s_full;
    var $e_full;

    /** @var int $cs_full Constant Full time start */
    /** @var int $ce_full Constant Full time end */
    var $cs_full;
    var $ce_full;

    /** @var int $offset Time offset */
    var $offset;

    public function start() {
        date_default_timezone_set("UTC");
        $this->b_s = '09:00:00';
        $this->b_e = '18:00:00';

        if ($this->debug) {
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $this->start = $time;
        }
        else {
            error_reporting(0);
        }
    }

    public function stopTimer() {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $this->start), 4);
        r('Page generated in '.$total_time.' seconds.');
    }

    public function connect() {
        $this->db = new RDS();
        $this->db->dbConnect($this->debug);
        $this->config = $this->db->config;
    }

    public function query($sql,$fetch = false,$fetchAll = false){
//        rt($sql);
        
		try {
            return $this->db->query($sql,$fetch,$fetchAll);
        }
        catch(Exception $e)
        {
            $this->answer($e,500);
        }
    }
	
	private function getJobs($id) {
		return $this->query("
			select id,user_id,name,company,current,start_time,end_time,type
			from user_jobs
			where user_id = $id
			",false,true);
	}
	
    public function getUsers($slots) {
		$slots = implode(',',$slots);
        $result = array();
        $uid = array();
        $uids = '';
        foreach ($this->free as $row) {
            $q_s = $row['start_time'];
            $q_e = $row['end_time'];
            $find = $this->query("
                SELECT c.user_id                                               AS id,
                       CASE
                         WHEN (SELECT id
                               FROM   calendar
                               WHERE  user_id = $this->user_id
                                  AND user_id_second = c.user_id
                                  AND `type` = 2
                                  AND `status` = 1
                               LIMIT  1) > 0 THEN 1
                         ELSE 0
                       end                                                     AS fp_request,
                       CASE
                         WHEN (SELECT id
                               FROM   calendar
                               WHERE  user_id = $this->user_id
                                  AND user_id_second = c.user_id
                                  AND `type` = 2
                                  AND `status` = 2
                               LIMIT  1) > 0 THEN 1
                         ELSE 0
                       end                                                     AS meet,
                       CASE
                         WHEN (SELECT id
                               FROM   user_friends
                               WHERE  user_id = $this->user_id
                                  AND `status` = 1
                                  AND friend_id = c.user_id
                               LIMIT  1) > 0 THEN 1
                         ELSE 0
                       end                                                     AS friend,
                       u.country,
                       u.name,
                       u.lastname,
                       u.email,
                       u.city_name,
                       u.city,
                       u.industry_id,
                       u.summary,
                       u.photo,
                       u.phone,
                       u.business_email,
                       u.skype,
                       u.rating,
                       u.experience,
                       u.completeness,
                       u.contacts,
                       u.meet_succesfull,
                       u.meet_declined,
                       Group_concat(DISTINCT s.name SEPARATOR '$$$$$')         AS skills,
                       Group_concat(DISTINCT l.languages_id SEPARATOR '$$$$$') AS languages,
                       GREATEST('$q_s', unix_timestamp(c.start_time)) as fp_start_time,
                       LEAST('$q_e', unix_timestamp(c.end_time))  as fp_end_time,
                       c.foursquare_id as fp_foursquare_id,
                       c.place as fp_place,
                       c.city as fp_city,
                       c.goal as fp_goal,
                       c.offset as fp_offset,
                       c.city_name as fp_city_name,
                       c.lat as fp_lat,
                       c.lng as fp_lng
                FROM   free_slots c
                       INNER JOIN (SELECT Min(start_time) AS start_time,
                                          type,
                                          user_id
                                   FROM   free_slots
                                   WHERE  id IN ( $slots )
                                   GROUP  BY `type`,
                                             user_id) f
                               ON c.user_id = f.user_id
                                  AND c.start_time = f.start_time
                                  AND c.type = f.type
                       LEFT JOIN users u
                              ON c.user_id = u.id
                       LEFT JOIN user_skills s
                              ON u.id = s.user_id
                       LEFT JOIN user_languages l
                              ON u.id = l.user_id
                $uids
                GROUP  BY c.user_id
                ORDER  BY c.type,
                          c.start_time
            ",false,true);

            foreach ($find as $row) {
                array_push($uid,$row['id']);
            }

            if ($uid) {
                $tp = implode(',',$uid);
                $uids = "WHERE u.id not in ($tp)";
            }

            $result = array_merge($result,$find);
        }
		foreach($result as $num=>$res) {
	        //Prepare Languages,Skills
	        if ($res['skills'] != null && $res['skills'] != '') {
	            $result[$num]['skills'] = explode('$$$$$',$res['skills']);
	        }
	        else {
	            $result[$num]['skills'] = array();
	        }
	        if ($res['languages'] != null && $res['languages'] != '') {
	            $result[$num]['languages'] = explode('$$$$$',$res['languages']);
	        }
	        else {
	            $result[$num]['languages'] = array();
	        }
			
			// Friends ?
            if ($res['friend']) {
                $result[$num]['friend'] = true;
            }
            else {
                unset($result[$num]['email']);
                unset($result[$num]['business_email']);
                unset($result[$num]['skype']);
                unset($result[$num]['phone']);
                $result[$num]['lastname'] = mb_substr($res['lastname'], 0, 1, 'UTF-8').'.';
                $result[$num]['friend'] = false;
            }
			
			
	        //Get user education
	        $result[$num]['education'] = array();
	        //Get UserJobs
	        $result[$num]['jobs'] = array();
			
			$expirience = $this->getJobs($res['id']);
			foreach ($expirience as $row) {
				if ($row['type'] == 0) {
					array_push($result[$num]['jobs'],$row);
				}
				else {
					array_push($result[$num]['education'],$row);
				}
			}
			
	        // Add photo
	        $url = $this->config['userPhoto.url'];
	        if ($res['photo'] != '' && $res['photo'] != null) {
	            $result[$num]['photo'] = $url.$res['photo'];
	        }
	        else {
	            $result[$num]['photo'] = null;
	        }
		}       
		
        return $result;
    }

    public function oldTime($s_full,$e_full) {
        $now = time();

        if ($e_full < $now) {
            return false;
        }

        if ($s_full < $now) {
            $s_full = $now;
        }

        $s_e = $e_full - $s_full;
        if ($s_e < 3600) {
            return false;
        }

        return array(
            'start' => $s_full,
            'end' => $e_full
        );
    }

    private function correctTimeOffset($s_time,$e_time,$s_date,$e_date) {
        if ( $s_time > $e_time ) {
            if ($this->offset > 0) {
                $s_date = $s_date - 86400;
            }
            elseif ($this->offset < 0) {
                $e_date = $e_date + 86400;
            }
        }

        return array(
            's_time' => $s_time,
            'e_time' => $e_time,
            's_date' => $s_date,
            'e_date' => $e_date,
        );
    }

    public function getFullTime() {
        // Get date
        $s_date = getdate($this->q_s);
        $s_date = mktime(0,0,0,$s_date['mon'],$s_date['mday'],$s_date['year']);
        $e_date = getdate($this->q_e);
        $e_date = mktime(0,0,0,$e_date['mon'],$e_date['mday'],$e_date['year']);

        // Get time
        $s_time = getdate($this->t_s);
        $s_time = $s_time['hours']*3600 + $s_time['minutes']*60 + $s_time['seconds'];
        $e_time = getdate($this->t_e);
        $e_time = $e_time['hours']*3600 + $e_time['minutes']*60 + $e_time['seconds'];

        $dt = $this->correctTimeOffset($s_time,$e_time,$s_date,$e_date);
        $s_time = $dt['s_time'];
        $e_time = $dt['e_time'];
        $s_date = $dt['s_date'];
        $e_date = $dt['e_date'];

        $this->s_full = $s_date + $s_time;
        $this->e_full = $e_date + $e_time;

        if ($s_time == $e_time) {
            $this->s_full = $this->s_full - 86400;
        }

        return array(
            's_date' => $s_date,
            's_time' => $s_time,
            'e_time' => $e_time,
        );
    }

    public function checkFree($suggest = false) {
        $dt = $this->getFullTime();
        $time = array();

        $s_e = $this->e_full - $this->s_full;
        if ($s_e <= 86400 && $s_e >= 3600) {
            if ($good = $this->oldTime($this->s_full,$this->e_full)) {
                $time = array(array(
                    'start_time' => $good['start'],
                    'end_time' => $good['end'],
                ));
            }
        }
        elseif ($s_e < 3600) {
            if ($suggest) {
                return false;
            }
            else {
                $this->answer('Bad time',414);
            }
        }
        else {
            $s_date = $dt['s_date'];
            $s_time = $dt['s_time'];
            $e_time = $dt['e_time'];

            while ($s_date < $this->e_full) {
                $s = $s_date + $s_time;
                $e = $s_date + $e_time;
                if ($e <= $s) {
                    $s = $s - 86400;
                }

                if ($good = $this->oldTime($s,$e)) {
                    $time[] = array(
                        'start_time' => $good['start'],
                        'end_time' => $good['end'],
                    );
                }
                $s_date = $s_date + 86400;
            }
        }
        if (!isset($time[0])) {
            if ($suggest) {
                return false;
            }
            else {
                $this->answer('Bad time',414);
            }
        }

        $slots_all = array();
        $meet_slots_all = array();
        foreach ($time as $row) {
            $this->q_s = $row['start_time'];
            $this->q_e = $row['end_time'];
            $allFree = array(array(
                "id" => 0,
                "user_id" => $this->user_id,
                "start_time" => $this->q_s,
                "end_time" => $this->q_e,
                "type" => 1,
                "is_free" => 0,
                "lat" => 0,
                "lng" => 0,
                "goal" => 0,
                "offset" => 0,
                "foursquare_id" => '',
                "place" => '',
                "city" => '',
                "city_name" => ''
            ));
            $meet_slots = $this->query("
            select c.id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time))  as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, c.type, s.value as is_free, i.lat, i.lng, i.goal, i.offset, i.foursquare_id, i.place, i.city, i.city_name
            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join calendar i on c.id = i.id
            where
                ((unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.start_time) <= '$this->q_e')
                or (unix_timestamp(c.end_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) <= '$this->q_s' and unix_timestamp(c.end_time) >= '$this->q_e'))
            and unix_timestamp(c.end_time) != $this->q_s
            and unix_timestamp(c.start_time) != $this->q_e
            and c.type = 2
            and c.status = 2
            and c.user_id = $this->user_id
        ",false,true);
            if ($meet_slots) {
                $slots = array_merge($allFree,$meet_slots);
                $slots = $this->findCrossOrNot($slots,1,2,true);
                if ($slots) {
                    $slots = $this->sortById($slots);
                    $slots = $this->addOrRemoveTime($slots,false);
                }
                else {
                    $slots = array(
                        array(
                            "start_time" => $this->q_s,
                            "end_time" => $this->q_e
                        )
                    );
                }
                $meet_slots_all = array_merge($meet_slots_all,$meet_slots);
            }
            else {
                $slots = array(
                    array(
                        "start_time" => $this->q_s,
                        "end_time" => $this->q_e
                    )
                );
            }

            $slots_all = array_merge($slots_all,$slots);
        }
        return $this->getMoreThanHourClear($slots_all,$meet_slots_all);
    }

    private function getMoreThanHourClear($slots,$meet_slots) {
        if ($result = $this->getMoreThanHour($slots)) {
            return $result;
        }
        else {
            $meet_slots_id = array();
            foreach ($meet_slots as $row) {
                array_push($meet_slots_id,$row['id']);
            }
            $meet_slots_id = implode(',',$meet_slots_id);
            $url = $this->config['userPhoto.url'];
            $meet_slots = $this->query("
               select c.id,c.user_id,c.user_id_second,unix_timestamp(c.start_time) as start_time,unix_timestamp(c.end_time) as end_time,c.goal,c.city,c.city_name,c.foursquare_id,c.place,c.lat,c.lng,c.rating,c.type,c.status,c.email,c.offset,
                 case
                  when c.email = 0 then case
                                          when (select distinct(f.id)
                                                from user_friends f
                                                where f.user_id = c.user_id
                                                and f.friend_id = u.id
                                                and f.status = 1 limit 1) > 0 then concat(u.name,' ',u.lastname)
                                          else concat(u.name,' ',substr(u.lastname,1,1),'.')
                                        end
                  else e.name
                 end as fullname,
                case
                  when c.email = 0 then concat('$url',u.photo)
                  else ''
                end as photo
                from calendar c
                left join users u on c.user_id_second = u.id and c.email = 0
                left join email_users e on c.user_id_second = e.id and c.email = 1
                where
                c.id in ($meet_slots_id)
            ",false,true);
            $this->answer($meet_slots,404);
        }
    }

    public function getMoreThanHour($slots) {
        $result = array();
        foreach ($slots as $row) {
            $time = (int)$row['end_time']-(int)$row['start_time'];
            if ($time >= 3600) {
                array_push($result,$row);
            }
        }
        return $result;
    }

    public function countUsers($slots) {
        $slots = implode(',',$slots);
        return (int)$this->query("
			select count(id) as id
			from
			(select user_id as id
			from free_slots
			where id in ( $slots )
			group by user_id) as t
		",true,false)['id'];
    }

    public function insertIntoFree($data,$db) {
        if ($data) {
            /** @var $db string Name of DB */
            $sql = "insert into $db (`user_id`, `start_time`, `end_time`, `type`, `is_free`, `lat`, `lng`, `goal`, `offset`, `foursquare_id`, `place`, `city`, `city_name`) VALUES ";

            $first = true;
            $i = 0;
            foreach ($data as $row) {
                $row['start_time'] = date('Y-m-d H:i:s',(int)$row['start_time']);
                $row['end_time'] = date('Y-m-d H:i:s',(int)$row['end_time']);
                $row['foursquare_id'] = $this->db->quote($row['foursquare_id']);
                $row['place'] = $this->db->quote($row['place']);
                $row['city'] = $this->db->quote($row['city']);
                $row['city_name'] = $this->db->quote($row['city_name']);

                if ($row['user_id'] && $row['start_time'] && $row['end_time'] && $row['type'] && is_numeric($row['offset']) && $row['city'] && $row['city_name']) {
                    if (!$first) {
                        $sql .= ',';
                    }
                    else {
                        $first = false;
                    };
                    $sql .= "(".$row['user_id'].",'".$row['start_time']."','".$row['end_time']."','".$row['type']."','".$row['is_free']."','".$row['lat']."','".$row['lng']."','".$row['goal']."','".$row['offset']."',".$row['foursquare_id'].",".$row['place'].",".$row['city'].",".$row['city_name'].")";
                }

                if ($i > 1000 && !$first) {
                    $this->query($sql);
                    $i = 0;
                    $first = true;
                    $sql = "insert into $db (`user_id`, `start_time`, `end_time`, `type`, `is_free`, `lat`, `lng`, `goal`, `offset`, `foursquare_id`, `place`, `city`, `city_name`) VALUES ";
                }

                $i++;
            }
            if (!$first) {
                $this->query($sql);
            }
        }
        return true;
    }



    public function getUser($token) {
        $token = $this->db->quote($token);
        $sql = "
            select `id`,`status`
            from users
            where private_key = $token
        ";
        $res = $this->query($sql,true);

        if (isset($res['status']) && $res['status'] == 0) {
            $this->user_id = $res['id'];
            return $res;
        }
        elseif (isset($res['status']) && $res['status'] != 0) {
            $this->answer('Current user blocked',407);
        }
        else {
            $this->answer('Have no permissions',401);
        }
    }

    public function answer($result,$code) {
        $this->db = null;
        if (!$this->debug) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'body' => $result,
                'errorCode' => $code
            ));
            die();
        }
        else {
            if ($this->debug) {
                if ($this->debug) $this->stopTimer();
                ~r($result);
            }
            else {
                echo json_encode(array(
                    'body' => 'Server Error',
                    'errorCode' => 500
                ));
                die();
            }
        }
    }

    public function sortByUser ($data) {
        usort($data, function ($a, $b) {
            return strcmp($a["user_id"], $b["user_id"]);
        });
        return $data;
    }

    public function sortById ($data) {
        usort($data, function ($a, $b) {
            return strcmp($a["id"], $b["id"]);
        });
        return $data;
    }

    public function setSlotsType ($data, $type) {
        $result = array();
        foreach ($data as $row) {
            $row['type'] = $type;
            array_push($result,$row);
        }
        return $result;
    }

    public function getSlots($id = '') {
        if ($id) {
            $id = " and c.user_id = $id";
        }

        $result = $this->query("
            select c.id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time))  as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, c.type, s.value as is_free, i.lat, i.lng, i.goal, i.offset, i.foursquare_id, i.place, i.city, i.city_name
            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join calendar i on c.id = i.id
            left join users u on c.user_id = u.id
            where
            (
                (unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
            )
            and
            (
                (c.type = 0 and s.value = '1')
                or (c.type = 2 and c.status = 2)
                or c.type = 1
            )
            and u.status = 0
            $id
        ",false,true);

        return $result;
    }


    public function getFreeSlots($id = '') {
        if ($id) {
            $id = " and s.user_id = $id";
        }

        $day = date('m/d/Y', $this->q_s);
        $b_s = strtotime($day.' '.$this->b_s);
        $b_e = strtotime($day.' '.$this->b_e);
        $data = $this->query("
            select concat('f',u.id) as id, u.id as user_id, $b_s - s2.value as start_time, $b_e - s2.value as end_time, 3 as type, s.value as is_free, u.free_lat as lat, u.free_lng as lng, s3.value as goal, s2.value as offset, u.free_foursquare_id as foursquare_id, u.free_place as place, u.free_city as city, u.free_city_name as city_name
            from user_settings s
            left join users u on s.user_id = u.id
            left join user_settings s2 on s2.user_id = u.id and s2.name = 'offset'
            left join user_settings s3 on s3.user_id = u.id and s3.name = 'goal'
            where
 			s.value = '1'
 			and s.name = 'free_time'
 			and u.status = 0
 			$id
        ",false,true);
        $result = $data;

        $q_s = $this->q_s;
        $q_e = $this->q_e;
        $i = 1;
        while ($q_s <= $q_e) {
            $q_s = strtotime('+1 day', $q_s);
            $one = $data;
            foreach ($data as $key => $val) {
                $one[$key]['start_time'] = strtotime("+$i day", $val['start_time']);
                $one[$key]['end_time'] = strtotime("+$i day", $val['end_time']);
            }
            $result = array_merge($result,$one);
            $i++;
        }
        return $result;
    }

    public function getSlotsByType($AllData,$type) {
        $result = array();
        foreach ($AllData as $row) {
            if ($row['type'] == $type) {
                array_push($result,$row);
            }
        }
        return $result;
    }

    public function findCrossOrNot($AllData,$typeA,$typeB,$cross) {
        $user_id = 0;

        $A = array();
        $B = array();
        $C = array();
        $was = array();
        $result = array();

        foreach ($AllData as $key => $row) {
            if ($user_id == $row['user_id']) {
                // All free
                if ($row['type'] == $typeA) {
                    array_push($A,$row);
                }
                // All busy
                elseif ($row['type'] == $typeB) {
                    array_push($B,$row);
                }
            }
            else {

                // Find cross
                foreach ($A as $Akey => $Aval) {
                    foreach ($B as $Bkey => $Bval) {
                        if ($Aval['start_time'] <= $Bval['end_time'] && $Aval['end_time'] >= $Bval['start_time']) {
                            $was[$Bkey] = $Akey;
//                            break;
                        }
                    }
                }

                // Delete cross
                foreach ($was as $wkey => $wval) {
                    if ($cross) {
                        // Add to free slot meeting time values
                        $A[$wval]['second_start_time'] =  $B[$wkey]['start_time'];
                        $A[$wval]['second_end_time'] =  $B[$wkey]['end_time'];
                        array_push($C,$A[$wval]);
                    }
                    else {
                        unset($A[$wval]);
                    }
                }

                if ($C && $cross) {
                    $result = array_merge($result,$C);
                }
                elseif ($A && !$cross)  {
                    $result = array_merge($result,$A);
                }

                // Clear help arrays
                $user_id = $row['user_id'];
                $A =  array();
                $B =  array();
                $was =  array();

                // All free
                if ($row['type'] == $typeA) {
                    $A[] =  $row;
                }
                // All busy
                elseif ($row['type'] == $typeB) {
                    $B[] =  $row;
                }
            }
        }

        // Process the last value
        if ($A) {
            // Find cross
            foreach ($A as $Akey => $Aval) {
                foreach ($B as $Bkey => $Bval) {
                    if ($Aval['start_time'] <= $Bval['end_time'] && $Aval['end_time'] >= $Bval['start_time']) {
                        $was[$Bkey] = $Akey;
//                        break;
                    }
                }
            }

            // Delete cross
            foreach ($was as $wkey => $wval) {
                if ($cross) {
                    // Add to free slot meeting time values
                    $A[$wval]['second_start_time'] =  $B[$wkey]['start_time'];
                    $A[$wval]['second_end_time'] =  $B[$wkey]['end_time'];
                    array_push($C,$A[$wval]);
                }
                else {
                    unset($A[$wval]);
                }
            }

            if ($C && $cross) {
                $result = array_merge($result,$C);
            }
            elseif ($A && !$cross)  {
                $result = array_merge($result,$A);
            }
        }

        return $result;
    }

    public function addOrRemoveTime($slots,$add) {
        if (!$slots) {
            return array();
        }

        $t_res = array();
        $res = array();
        $busy = array();

        // Get first slot
        $last = $slots[0];
        unset($slots[0]);
        array_push($busy,$last);

        foreach ($slots as $row) {
            if ($last['id'] != $row['id']) {

                unset($last['second_start_time']);
                unset($last['second_end_time']);
                $t_res[] = $last;

                foreach ($busy as $busy_s) {
                    $l_res = $t_res;
                    $t_res = array();
                    foreach ($l_res as $tt_res) {
                        // left side
                        if ($tt_res['start_time'] <= $busy_s['second_start_time']) {
                            $free = $tt_res;

                            $free['end_time'] = ($add) ? max($busy_s['second_start_time'], $tt_res['end_time']):
                                                         min($busy_s['second_start_time'], $tt_res['end_time']);

                            array_push($t_res,$free);
                        }

                        // right side
                        if ($tt_res['end_time'] >= $busy_s['second_end_time']) {
                            $free = $tt_res;
                            $free['start_time'] = ($add) ? min($busy_s['second_end_time'], $tt_res['start_time']):
                                                           max($busy_s['second_end_time'], $tt_res['start_time']);

                            array_push($t_res,$free);
                        }
                    }
                }

                $busy = array();
                array_push($busy,$row);

                $res = array_merge($res,$t_res);
                $t_res = array();
            }
            else {
                array_push($busy,$row);
            }
            $last = $row;
        }

        if (isset($last['id'])) {
            unset($last['second_start_time']);
            unset($last['second_end_time']);
            $t_res[] = $last;

            foreach ($busy as $busy_s) {
                $l_res = $t_res;
                $t_res = array();
                foreach ($l_res as $tt_res) {
                    // left side
                    if ($tt_res['start_time'] <= $busy_s['second_start_time']) {
                        $free = $tt_res;

                        $free['end_time'] = ($add) ? max($busy_s['second_start_time'], $tt_res['end_time']):
                                                     min($busy_s['second_start_time'], $tt_res['end_time']);

                        array_push($t_res,$free);
                    }

                    // right side
                    if ($tt_res['end_time'] >= $busy_s['second_end_time']) {
                        $free = $tt_res;
                        $free['start_time'] = ($add) ? min($busy_s['second_end_time'], $tt_res['start_time']):
                                                       max($busy_s['second_end_time'], $tt_res['start_time']);

                        array_push($t_res,$free);
                    }
                }
            }
            $res = array_merge($res,$t_res);
        }

        return $res;
    }

}