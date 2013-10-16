<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/helpers"
 * )
 */

include_once 'Common.class.php';


/**
 *
 * @SWG\Model(id="FindPeopleParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="debug",type="boolean")
 *
 * @SWG\Property(name="data_from",type="timestamp")
 * @SWG\Property(name="data_to",type="timestamp") *
 * @SWG\Property(name="city",type="string")
 * @SWG\Property(name="goal",type="int")
 * @SWG\Property(name="industry",type="int")
 *
 * @SWG\Property(name="map",type="boolean")
 * @SWG\Property(name="lat",type="float")
 * @SWG\Property(name="lng",type="float")
 *
 *
 *
 * @SWG\Api(
 *   path="/findpeople.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       httpMethod="POST",
 *       summary="Find People.",
 *       responseClass="void",
 *       nickname="FindPeople",
 *       notes="",
 *       @SWG\ErrorResponses(
 *          @SWG\ErrorResponse(
 *            code="400",
 *            reason="Not all params correct."
 *          ),
 *           @SWG\ErrorResponse(
 *            code="401",
 *            reason="Have no permissions."
 *          ),
 *           @SWG\ErrorResponse(
 *            code="407",
 *            reason="Current user blocked."
 *          ),
 *           @SWG\ErrorResponse(
 *            code="404",
 *            reason="You have`n free time. for request period."
 *          ),
 *           @SWG\ErrorResponse(
 *            code="500",
 *            reason="Server side problem."
 *          )
 *       ),
 * @SWG\Parameter(
 *           name="json",
 *           description="json",
 *           paramType="body",
 *           required="true",
 *           allowMultiple="false",
 *           dataType="FindPeopleParams"
 *         )
 *     )
 *   )
 * )
 */
class FindPeople extends Common {

    /** @var array $free Free time of user who request */
    var $free = array();

    /** @var string $b_s Bussines time start */
    /** @var string $b_e Bussines time end */
    /** @var string $q_s Query time start */
    /** @var string $q_e Query time end */
    /** @var int $goal Query goal */
    /** @var int $industry Query industry */
    /** @var float $n_lat Query city square */
    /** @var float $s_lat Query city square */
    /** @var float $n_lng Query city square */
    /** @var float $s_lng Query city square */
    var $b_s;
    var $b_e;
    var $q_s;
    var $q_e;
    var $goal;
    var $industry;
    var $n_lat;
    var $s_lat;
    var $n_lng;
    var $s_lng;
    var $industry_q;
    var $goal_r;
    var $goal_nf;
    var $goal_fn;
    var $goal_f;




    public function __construct($debug,$map,$data) {
        $this->debug = $debug;
        $this->map = $map;
        $this->data = $data;
        $this->mainWork();
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
                $name = $req['result']['formatted_address'];
                $data['city'] = $city;
                $lat = $req['result']['geometry']['location']['lat'];
                $lng = $req['result']['geometry']['location']['lng'];

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

    public function mapUpdate($user_id,$lat,$lng,$offset) {
        $sql = "
            select id
            from `map`
            where user_id = $user_id
        ";

        $result = $this->query($sql,true);

        $time = date('Y-m-d H:i:s',time());
        if (isset($result['id'])) {
            $id = $result['id'];

            $sql = "
                    update `map` set `lat` = $lat, `lng` = $lng, `time` = '$time', `offset` = $offset
                    where id = $id and user_id = $user_id
                ";
            $this->query($sql);
        }
        else {
            $sql = "
                    INSERT INTO `map` (`user_id`, `lat`, `lng`, `time`, `offset`)
                    VALUES
	                ($user_id,$lat,$lng,'$time','$offset')
                ";
            $this->query($sql);
        }
    }

    public function checkFree(){
        $sql = "
                create temporary table uSult (`id` bigint(20) unsigned DEFAULT NULL,
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
                `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
                `start_time` timestamp NULL DEFAULT NULL,
                `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY
            ";
        $this->query($sql);

        $sql = "
                insert into uSult (id, user_id, `type`, is_free, start_time, end_time)
                values (1, $this->user_id, 1, 0, '$this->q_s', '$this->q_e' )
            ";
        $this->query($sql);

        $sql = "
                select t.id, t.user_id, t.type, 0 as is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
                from uSult t
                inner JOIN calendar mt on t.user_id = mt.user_id
                and mt.type = 2
                and mt.status = 2
                and t.start_time <= mt.end_time
                and t.end_time >= mt.start_time

                order by t.id,mt.start_time
            ";
        $result = $this->query($sql);

        if ($result->rowCount() > 0) {
            $result = $this->getFreeArray($result);
            $this->query("truncate table uSult");
            $this->insertInto($result,'uSult');
        }


        $sql = "
                select r.start_time, r.end_time
                from uSult r
                where (UNIX_TIMESTAMP(r.end_time) - UNIX_TIMESTAMP(r.start_time)) >= 3600
            ";
        $result = $this->query($sql,false,true);

        $this->query("drop table uSult");
        return $result;
    }

    public function getValues() {
        $data = $this->data;

        if (!$this->map) {
            $che = true;
            if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;

            if (isset($data["data_from"])) $this->q_s = $data["data_from"]; else $che = false;
            if (isset($data["data_to"])) $this->q_e = $data["data_to"]; else $che = false;

            if (isset($data["city"])) $city = $data["city"]; else $che = false;

            if (isset($data["goal"])) $this->goal = $data["goal"];
            if (isset($data["industry"])) $this->industry = $data["industry"];

            if (!$che) {
                $this->answer('Not all params given',400);
                die();
            }
            if ( $token != null && $token != '' && is_numeric($this->q_s) && is_numeric($this->q_e) &&  $city != null && $city != '') {
                $this->getUser($token);
                $city = $this->getCity($city);
                if ($city) {
                    $this->b_s = date("Y-m-d", $this->q_s).' 09:00:00';
                    $this->b_e = date("Y-m-d", $this->q_e).' 18:00:00';

                    $this->q_s = date("Y-m-d H:i:s", $this->q_s);
                    $this->q_e = date("Y-m-d H:i:s", $this->q_e);

                    $this->free = $this->checkFree();

                    $this->n_lat = $city['n_lat'];
                    $this->s_lat = $city['s_lat'];
                    $this->n_lng = $city['n_lng'];
                    $this->s_lng = $city['s_lng'];
                }
                else {
                    $this->answer('Bad city',400);
                    die();
                }
            }
            else {
                $this->answer('Not all params given',400);
                die();
            }
        }
        else {
            $che = true;

            if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;

            if (isset($data["lat"])) $lat = $data["lat"]; else $che = false;
            if (isset($data["lng"])) $lng = $data["lng"]; else $che = false;
            if (isset($data["offset"])) $offset = $data["offset"]; else $che = false;

            if (!$che) {
                $this->answer('Not all params given',400);
                die();
            }


            $this->n_lat = (float)$data["lat"]+1.5;
            $this->s_lat = (float)$data["lat"]-1.5;
            $this->n_lng = (float)$data["lng"]+1.5;
            $this->s_lng = (float)$data["lng"]-1.5;

            if ( $token != null && $token != '' && is_numeric($this->n_lat) && is_numeric($this->s_lat) && is_numeric($this->n_lng) && is_numeric($this->s_lng) && is_numeric($lat) && is_numeric($lng)) {
                $user = $this->getUser($token);
                if ($user) {
                    $this->mapUpdate($user['id'],$lat,$lng,$offset);

                    $time = time();

                    $this->q_s = date("Y-m-d H:i:s", $time );
                    $this->q_e = date("Y-m-d H:i:s", $time + 7200 );

                    $this->free = $this->checkFree();

                    $this->b_s = date("Y-m-d", $time).' 09:00:00';
                    $this->b_e = date("Y-m-d", $time).' 18:00:00';
                }
                else {
                    $this->answer('Authentication failed',401);
                    die();
                }
            }
            else {
                $this->answer('Not all params given',400);
                die();
            }
        }


        if (isset($this->industry) && is_numeric($this->industry)) {
            $this->industry_q = " and u.industry_id = $this->industry ";
        }
        else {
            $this->industry_q = " ";
        }

        if (isset($this->goal) && is_numeric($this->goal)) {
            $this->goal_r = " select in_id from goals where id = $this->goal into @goals; ";
            $this->goal_nf = " and c.goal in (@goals) ";
            $this->goal_f = " and (select cast(s3.value as unsigned) from user_settings s3 where s3.user_id = c.user_id and s3.name = 'goal') in (@goals) ";
            $this->goal_fn = " and (select cast(s3.value as unsigned) from user_settings s3 where s3.user_id = u.id and s3.name = 'goal') in (@goals) ";
        }
        else {
            $this->goal_r = "";
            $this->goal_nf = " ";
            $this->goal_f = " ";
            $this->goal_fn = " ";
        }

    }

    public function createT() {


        $this->query("
            create table $this->temp_t (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");

        $this->query("
            create temporary table rSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");

        $this->query("
            create temporary table zSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");

        $this->query("
            create temporary table xSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");

        if ($this->goal_r) {
            $this->query("
                $this->goal_r
            ");
        }


        $this->query("
            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select c.id as id, c.user_id, c.type, s.value as is_free,
            CASE
                WHEN c.type=1 THEN GREATEST('$this->q_s', c.start_time)
                ELSE c.start_time
            end AS start_time,
            CASE
                WHEN c.type=1 THEN LEAST('$this->q_e', c.end_time)
                ELSE c.end_time
            end AS end_time

            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join users u on c.user_id = u.id

            where
            ( (c.start_time between '$this->q_s' and '$this->q_e') or (c.end_time between '$this->q_s' and '$this->q_e') or (c.start_time >= '$this->q_s' and c.end_time <= '$this->q_e') )

            and ( (c.type = 2 and c.status = 2)
            or ( c.type = 1
            and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_nf
            ))

            and (s.value is null or s.value = '0')
            and u.status = 0
            having start_time != end_time;
        ");

        $this->query("
            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select c.id, c.user_id, c.type, s.value as is_free,
            CASE
                WHEN c.type=1 THEN GREATEST('$this->q_s', c.start_time)
                ELSE c.start_time
            end AS start_time,
            CASE
                WHEN c.type=1 THEN LEAST('$this->q_e', c.end_time)
                ELSE c.end_time
            end AS end_time

            from calendar c
            left join user_settings s on s.user_id = c.user_id and s.name = 'free_time'
            left join users u on c.user_id = u.id

            where
            ( (c.start_time between '$this->q_s' and '$this->q_e') or (c.end_time between '$this->q_s' and '$this->q_e') or (c.start_time >= '$this->q_s' and c.end_time <= '$this->q_e') )

            and (
               ( c.type = 2 and c.status = 2)
            or ( c.type = 1
                and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
                and c.goal in (@goals)
               )
            or ( c.type = 0 and c.status = 0)
            )

            and u.status = 0
            and s.value = '1'
            and u.free_lng BETWEEN $this->s_lng AND $this->n_lng AND u.free_lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_f

            having start_time != end_time;
        ");

        $this->query("
            insert into $this->temp_t (id, user_id, `type`, is_free, start_time, end_time)
            select u.id as id, u.id as user_id, 3 as type, s.value as is_free, GREATEST('$this->q_s', '$this->b_s' - INTERVAL s2.value second ) as start_time, LEAST('$this->q_e', '$this->b_e' - INTERVAL s2.value second ) as end_time

            from users u
            left join user_settings s on s.user_id = u.id and s.name = 'free_time'
            left join user_settings s2 on s2.user_id = u.id and s2.name = 'offset'

            where
            s.value = '1'
            and u.status = 0
            and u.free_lng BETWEEN $this->s_lng AND $this->n_lng AND u.free_lat BETWEEN $this->s_lat AND $this->n_lat
            $this->industry_q
            $this->goal_fn
            having start_time != end_time;
        ");

        $this->query("
            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            (
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN $this->temp_t mt on t.user_id = mt.user_id
            and mt.type = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            and mt.id is null
            );
        ");
    }

    public function MeetFreeCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN $this->temp_t mt on t.user_id = mt.user_id
            and mt.type = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            order by t.id,mt.start_time
        ";
        $result = $this->query($sql);
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function BusyRNotCross() {
        $sql = "
            insert into zSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN rSult ft on t.user_id = ft.user_id
            and ft.type = 1
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 0
            and t.is_free = 1
            and ft.id is null
        ";
        $this->query($sql);
    }

    public function BusyRCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN rSult mt on t.user_id = mt.user_id
            and mt.type = 1
            and mt.is_free = 1
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 0
            and t.is_free = 1
            order by t.id,mt.start_time
        ";

        $result = $this->query($sql);
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'zSult');
    }

    public function BusyPlusMeet() {
        $sql = "
            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN zSult ft on t.user_id = ft.user_id
            and ft.type = 0
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 2
            and t.is_free = 1
            and ft.id is null;

            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from zSult t
            left outer JOIN $this->temp_t ft on t.user_id = ft.user_id
            and ft.type = 2
            and ft.is_free = 1
            and t.start_time <= ft.end_time
            and t.end_time >= ft.start_time

            where t.type = 0
            and t.is_free = 1
            and ft.id is null;

            insert into xSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, 0 as 'type', t.is_free, LEAST(t.start_time, mt.start_time) AS start_time, greatest(t.end_time, mt.end_time) AS end_time
            from $this->temp_t t
            inner JOIN zSult mt on t.user_id = mt.user_id
            and mt.type = 0
            and mt.is_free = 1
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 2
            and t.is_free = 1;
        ";

        try {
            $stmt = $this->mysql->prepare($sql);
            $stmt->execute();
        }
        catch(Exception $e)
        {
            $this->answer($e,500);
            $this->clearTempData();
            die();
        }
    }

    public function SetXNotCross() {
        $sql = "
            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            select t.id, t.user_id, t.type, t.is_free, t.start_time, t.end_time
            from $this->temp_t t
            left outer JOIN xSult ft on t.user_id = ft.user_id
            and t.start_time < ft.end_time
            and t.end_time > ft.start_time

            where t.type = 3
            and t.is_free = 1
            and ft.id is null
        ";
        $this->query($sql);
    }

    public function SetXCross() {
        $sql = "
            select t.id, t.user_id, t.type, t.is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from $this->temp_t t
            inner JOIN xSult mt on t.user_id = mt.user_id
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 3
            and t.is_free = 1
            order by t.id,mt.start_time
        ";

        $result = $this->query($sql);
        $result = $this->getFreeArray($result);
        $this->insertInto($result,'rSult');
    }

    public function getResult() {
        if (!$this->map) {
            $sql = "
                select t.user_id, t.start_time, t.end_time,
                    case
                      when t.type = 1 then c.foursquare_id
                      when t.type = 3 then u.free_foursquare_id
                      else null
                    end as foursquare_id,
                    case
                      when t.type = 1 then c.place
                      when t.type = 3 then u.free_place
                      else null
                    end as place,
                    case
                      when (select count(o.id) from calendar o where o.user_id = $this->user_id and o.user_id_second = t.user_id and o.type = 2 and o.status = 1 and o.start_time > now() limit 1) > 0 then 1
                      else 0
                    end as request,
                    case
                      when (select distinct(f.id)
                            from user_friends f
                            where f.user_id = $this->user_id
                            and f.friend_id = t.user_id
                            and f.status = 1 limit 1) > 0 then 1
                      else 0
                    end as friend,
                    case
                          when t.type = 1 then c.city
                          when t.type = 3 then u.free_city
                          else null
                    end as city,
                    case
                          when t.type = 1 then c.goal
                          when t.type = 3 then s4.value
                          else null
                    end as goal,
                    case
                          when t.type = 1 then c.offset
                          when t.type = 3 then s2.value
                          else null
                    end as offset,
                    case
                          when t.type = 1 then c.city_name
                          when t.type = 3 then u.free_city_name
                          else null
                    end as city_name
                from (select * from rSult order by start_time asc) as t
                left join calendar c on t.id = c.id
                left join users u on t.user_id = u.id
                left join user_settings s2 on t.user_id = s2.user_id and t.type = 3 and s2.name = 'offset'
                left join user_settings s4 on t.user_id = s4.user_id and t.type = 3 and s4.name = 'goal'
                where (UNIX_TIMESTAMP(t.end_time) - UNIX_TIMESTAMP(t.start_time)) >= 3600
                and t.user_id != $this->user_id
                group by t.user_id
                order by request,friend asc
            ";
        }
        else {
            $url = $this->config['userPhoto.url'];
            $sql = "
                select t.user_id, t.lat, t.lng, u.name, u.lastname, concat('$url',u.photo) as photo, j.name as job_name, j.company, u.industry_id, u.rating, t.foursquare_id, t.place, t.start_time, u.city_name, offset
                from (
                    (
                        SELECT m.user_id, m.lat, m.lng, unix_timestamp(now()) as start_time,
                        null as foursquare_id,
                        null as place,
                        m.offset
                        FROM map m
                        WHERE
                        m.time > now() - interval 10 MINUTE
                        and m.lng BETWEEN $this->s_lng AND $this->n_lng AND m.lat BETWEEN $this->s_lat AND $this->n_lat
                    )
                    union
                    (
                        select r.user_id,
                        case
                          when r.type = 1 then c.lng
                          when r.type = 3 then u.free_lat
                          else null
                        end as lat,
                        case
                          when r.type = 1 then c.lng
                          when r.type = 3 then u.free_lng
                          else null
                        end as lng,
                        r.start_time,
                        case
                          when r.type = 1 then c.foursquare_id
                          when r.type = 3 then u.free_foursquare_id
                          else null
                        end as foursquare_id,
                        case
                          when r.type = 1 then c.place
                          when r.type = 3 then u.free_place
                          else null
                        end as place,
                        case
                          when r.type = 1 then c.offset
                          when r.type = 3 then s2.value
                          else 0
                        end as 'offset'
                        from rSult r
                        left join calendar c on r.id = c.id
                        left join users u on r.user_id = u.id
                        left join user_settings s2 on r.user_id = s2.user_id and r.type = 3 and s2.name = 'offset'
                        where (UNIX_TIMESTAMP(r.end_time) - UNIX_TIMESTAMP(r.start_time)) >= 3600
                    )
                ) as t
                left join users u on t.user_id = u.id
                left join user_jobs j on t.user_id = j.user_id
                WHERE t.user_id != $this->user_id
                and j.current=1
                group by t.user_id
            ";
        }

        $res = $this->query($sql,false,true);

        return $res;
    }

    public function insertM($data) {
        if ($data) {
            $sql = "insert into mSult (user_id, start_time, end_time, foursquare_id, place, request, friend, city, goal, city_name, offset) VALUES ";

            $first = true;
            foreach ($data as $row) {
                if (!$first) {
                    $sql .= ',';
                };
                $first = false;
                $sql .= "(".$row['user_id'].",'".$row['start_time']."','".$row['end_time']."','".$row['foursquare_id']."',".$this->mysql->quote($row['place']).",'".$row['request']."','".$row['friend']."','".$row['city']."','".$row['goal']."','".$row['city_name']."','".$row['offset']."')";
            }

            $this->query($sql);
        }
        return true;
    }

    public function mainWork() {
        $this->connect();
        $this->getValues();
        if ((int)count($this->free) === 1) {
            $this->q_s = $this->free[0]['start_time'];
            $this->q_e = $this->free[0]['end_time'];
            $result = $this->find();
            $this->answer($result,200);
        }
        elseif ((int)count($this->free) === 0) {
            $this->answer('You have`n free time. for request period',404);
        }
        else {
            $sql = "
                create temporary table mSult (
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `request` int(2) unsigned DEFAULT NULL,
                `friend` int(2) unsigned DEFAULT NULL,
                `start_time` timestamp NULL DEFAULT NULL,
                `end_time` timestamp NULL DEFAULT NULL,
                `foursquare_id` varchar(255) NULL DEFAULT NULL,
                `place` varchar(255) NULL DEFAULT NULL,
                `city` varchar(255) NULL DEFAULT NULL,
                `city_name` varchar(255) NULL DEFAULT NULL,
                `offset` int(11) NULL DEFAULT NULL,
                `goal` int(11) NULL DEFAULT NULL) ENGINE=MEMORY DEFAULT CHARSET=utf8;
            ";
            $this->query($sql);
            foreach ($this->free as $row) {
                $this->q_s = $row['start_time'];
                $this->q_e = $row['end_time'];
                $this->insertM($this->find());
            }
            $answer = $this->query("
                select user_id, start_time, end_time, foursquare_id, place, request, friend, city, goal, city_name, offset
                from mSult
                group by user_id
                order by request,friend asc
            ",false,true);
            $this->query('drop table mSult');

            $this->answer($answer,200);
        }
    }

    public function find() {
        $this->temp_t = '`'.uniqid().'`';
        $this->createT();

        // Case A
        $this->MeetFreeCross();

        // Case B

        // Busy minus R
        $this->BusyRNotCross();
        $this->BusyRCross();

        // Busy plus meet
        $this->BusyPlusMeet();

        // Set - X
        $this->SetXNotCross();
        $this->SetXCross();

        // Get Result and Drop temporary data
        $result = $this->getResult();
        $this->clearTempData(true);
        return $result;
    }
}