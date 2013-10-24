<?php

include_once 'RDS.class.php';

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

    /** @var int $b_s Bussines time start */
    /** @var int $b_e Bussines time end */
    var $b_s;
    var $b_e;

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
        // r($sql);
        
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
		$result = $this->query("
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
			       end                                                     AS request,
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
			       c.start_time,
			       c.end_time,
			       c.foursquare_id,
			       c.place,
			       c.city,
			       c.goal,
			       c.offset,
			       c.city_name
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
			GROUP  BY c.user_id
			ORDER  BY c.type,
			          c.start_time
		",false,true);
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
            if ($friends) {
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

    public function checkFree(){
        $allFree = array(
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
        );
        $slots = $this->query("
            select c.id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time))  as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, c.type, s.value as is_free, i.lat, i.lng, i.goal, i.offset, i.foursquare_id, i.place, i.city, i.city_name
            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join calendar i on c.id = i.id
            where
            (
                (unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
            )
            and c.type = 2
            and c.status = 2
            and c.user_id = $this->user_id
        ",false,true);
        if ($slots) {
            $slots = array_merge($allFree,$slots);
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
        }
        else {
            $slots = array(
                array(
                    "start_time" => $this->q_s,
                    "end_time" => $this->q_e
                )
            );
        }
        return $this->getMoreThanHourClear($slots);
    }

    public function getMoreThanHourClear($slots) {
        $result = array();
        foreach ($slots as $row) {
            $time = (int)$row['end_time']-(int)$row['start_time'];
            if ($time >= 3600) {
                array_push($result,array(
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time']
                ));
            }
        }
        return $result;
    }

    public function insertIntoFree($data,$db) {
        if ($data) {
            /** @var $db string Name of DB */
            $sql = "insert into $db (`user_id`, `start_time`, `end_time`, `type`, `is_free`, `lat`, `lng`, `goal`, `offset`, `foursquare_id`, `place`, `city`, `city_name`) VALUES ";

            $first = true;
            foreach ($data as $row) {
                $row['start_time'] = date('Y-m-d H:i:s',(int)$row['start_time']);
                $row['end_time'] = date('Y-m-d H:i:s',(int)$row['end_time']);
                $row['foursquare_id'] = $this->db->quote($row['foursquare_id']);
                $row['place'] = $this->db->quote($row['place']);
                $row['city'] = $this->db->quote($row['city']);
                $row['city_name'] = $this->db->quote($row['city_name']);

                if (!$first) {
                    $sql .= ',';
                };
                $first = false;
                $sql .= "(".$row['user_id'].",'".$row['start_time']."','".$row['end_time']."','".$row['type']."','".$row['is_free']."','".$row['lat']."','".$row['lng']."','".$row['goal']."','".$row['offset']."',".$row['foursquare_id'].",".$row['place'].",".$row['city'].",".$row['city_name'].")";
            }
            $this->query($sql);
        }
        return true;
    }

    public function getCity($city) {
        $sql = "
            select city, n_lat, n_lng, s_lat, s_lng
            from city
            where city = '$city'
        ";

        $result = $this->query($sql,true);

        if (isset($result['city'])) {
            return $result;
        }
        else {
            $url = $this->config['google.url'].(string)$city;
            $req = json_decode(file_get_contents($url), true);

            if ($req['status'] == 'OK') {
                $data['city'] = $city;
                $lat = $req['result']['geometry']['location']['lat'];
                $lng = $req['result']['geometry']['location']['lng'];

                foreach ($req['result']['address_components'] as $row) {
                    if ($row['types']) {
                        foreach ($row['types'] as $row2) {
                            if ($row2 == 'country') {
                                $name = $req['result']['name'].', '.$row['short_name'];
                            }
                        }
                    }
                }

                if (isset($req['result']['geometry']['viewport'])) {
                    $data['n_lng'] = $req['result']['geometry']['viewport']['northeast']['lng'];
                    $data['s_lng'] = $req['result']['geometry']['viewport']['southwest']['lng'];
                    $data['n_lat'] = $req['result']['geometry']['viewport']['northeast']['lat'];
                    $data['s_lat'] = $req['result']['geometry']['viewport']['southwest']['lat'];
                }
                else {
                    $data['n_lng'] = (float)$data['lng']+0.1;
                    $data['s_lng'] = (float)$data['lng']-0.1;
                    $data['n_lat'] = (float)$data['lat']+0.1;
                    $data['s_lat'] = (float)$data['lat']-0.1;
                }

                $s_lng = $data['s_lng'];
                $n_lng = $data['n_lng'];
                $n_lat = $data['n_lat'];
                $s_lat = $data['s_lat'];

                $sql = "
                    INSERT INTO `city` (`city`, `city_name`, `lat`, `lng`, `n_lat`, `n_lng`, `s_lat`, `s_lng`)
                    VALUES
	                ('$city', '$name', $lat, $lng, $n_lat, $n_lng, $s_lat, $s_lng)
                ";
                $this->query($sql);

                return $data;
            }
            else {
                return false;
            }
        }
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
                            break;
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
                        break;
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