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
 * @SWG\Model(id="MapParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="debug",type="boolean")
 *
 * @SWG\Property(name="offset",type="int")
 * @SWG\Property(name="lat",type="float")
 * @SWG\Property(name="lng",type="float")
 *
 *
 *
 * @SWG\Api(
 *   path="/map.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       httpMethod="POST",
 *       summary="Map people.",
 *       responseClass="void",
 *       nickname="Map",
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
 *			@SWG\ErrorResponse(
 *            code="410",
 *            reason="No one found."
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
 *           dataType="MapParams"
 *         )
 *     )
 *   )
 * )
 */
class Map extends Common {

    /** @var int $q_s Query time start */
    /** @var int $q_e Query time end */
    var $q_s;
    var $q_e;

    /** @var int $t_s Query time start */
    /** @var int $t_e Query time end */
    var $t_s;
    var $t_e;

    /** @var float $n_lat Query square */
    /** @var float $s_lat Query square */
    /** @var float $n_lng Query square */
    /** @var float $s_lng Query square */
    var $n_lat;
    var $s_lat;
    var $n_lng;
    var $s_lng;

    /** @var int $user_id Id of user */
    var $user_id;


    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
    }

    private function mapUpdate($lat,$lng,$offset) {
        $result = $this->query("
            select id
            from `map`
            where user_id = $this->user_id
        ",true);

        $time = date('Y-m-d H:i:s',time());
        if (isset($result['id'])) {
            $id = $result['id'];
            $this->query("
                    update `map` set `lat` = $lat, `lng` = $lng, `time` = '$time', `offset` = $offset
                    where id = $id and user_id = $this->user_id
            ");
        }
        else {
            $this->query("
                    INSERT INTO `map` (`user_id`, `lat`, `lng`, `time`, `offset`)
                    VALUES
	                ($this->user_id,$lat,$lng,'$time','$offset')
            ");
        }
    }

    public function getValues($data) {
        $che = true;

        // Get all params
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["lat"])) $lat = $data["lat"]; else $che = false;
        if (isset($data["lng"])) $lng = $data["lng"]; else $che = false;
        if (isset($data["offset"])) $offset = $data["offset"]; else $che = false;

        if (!$che) $this->answer('Not all params given',400);

        // Check params
        if ( $token != null && $token != '' && is_numeric($lat) && is_numeric($lng) &&  is_numeric($offset)) {
            $this->getUser($token);

            $this->q_s = time();
            $this->q_e = $this->q_s + 7200;

            $this->t_s = $this->q_s;
            $this->t_e = $this->q_e;

            $this->free = $this->checkFree();
            $this->mapUpdate($lat,$lng,$offset);

            $this->n_lat = (float)$lat+1.5;
            $this->s_lat = (float)$lat-1.5;
            $this->n_lng = (float)$lng+1.5;
            $this->s_lng = (float)$lng-1.5;
        }
        else
            $this->answer('Not all params given',400);
    }

    public function findUsers() {
        $url = $this->config['userPhoto.url'];
        $result = array();
        foreach ($this->free as $row) {
            $this->q_s = $row['start_time'];
            $this->q_e = $row['end_time'];
            $find = $this->query("
                select t.user_id, t.lat, t.lng, u.name, u.lastname, concat('$url',u.photo) as photo, j.name as job_name, j.company, u.industry_id, u.rating, t.foursquare_id, t.place, GREATEST('$this->q_s', unix_timestamp(t.start_time)) as start_time, LEAST('$this->q_e', unix_timestamp(t.end_time)) as end_time, u.city_name, offset, t.goal
                    from (
                        (
                            SELECT m2.user_id, m.lat, m.lng, now() as start_time, now()+3600 as end_time, null as foursquare_id, null as place, m2.offset, 0 as goal
                            FROM map m
                            left join map m2 on m.id=m2.id
                            WHERE
                            m.time > now() - interval 10 MINUTE
                            and m.lng BETWEEN $this->s_lng AND $this->n_lng AND m.lat BETWEEN $this->s_lat AND $this->n_lat
                        )
                        union
                        (
                            select c.user_id, c.lat, c.lng, c.start_time, c.end_time, c2.foursquare_id, c2.place, c2.offset, c2.goal as goal
                            from free_slots c
                            left join free_slots c2 on c.id = c2.id
                            where
                                ((unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.start_time) <= '$this->q_e')
                                or (unix_timestamp(c.end_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                                or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                                or (unix_timestamp(c.start_time) <= '$this->q_s' and unix_timestamp(c.end_time) >= '$this->q_e'))
                                and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
                        )
                    ) as t
                    left join users u on t.user_id = u.id
                    left join user_jobs j on t.user_id = j.user_id
                    WHERE
                    j.current=1
                    and u.status = 0
                    and u.id != $this->user_id
                    group by t.user_id
                    having ( end_time - start_time ) >= 3600
                    limit 500
            ",false,true);
            $result = array_merge($result,$find);
        }
        return $result;
    }

}