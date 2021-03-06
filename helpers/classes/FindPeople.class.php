<?php


/**
 * @SWG\Resource(
 *  resourcePath="/helpers"
 * )
 */

require 'Common.class.php';


/**
 *
 * @SWG\Model(id="FindPeopleParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="debug",type="boolean")
 *
 * @SWG\Property(name="offset",type="integer")
 * @SWG\Property(name="data_from",type="timestamp")
 * @SWG\Property(name="data_to",type="timestamp")
 * @SWG\Property(name="time_from",type="timestamp")
 * @SWG\Property(name="time_to",type="timestamp")
 * @SWG\Property(name="city",type="string")
 * @SWG\Property(name="goal",type="integer")
 * @SWG\Property(name="industry",type="integer")
 *
 *
 *
 * @SWG\Api(
 *   path="/findPeople.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       method="POST",
 *       summary="Find People.",
 *       type="void",
 *       nickname="FindPeople",
 *       notes="",
 *       @SWG\ResponseMessages(
 *          @SWG\ResponseMessage(
 *            code="400",
 *            message="Not all params correct."
 *          ),
 *           @SWG\ResponseMessage(
 *            code="401",
 *            message="Have no permissions."
 *          ),
 *           @SWG\ResponseMessage(
 *            code="407",
 *            message="Current user blocked."
 *          ),
 *           @SWG\ResponseMessage(
 *            code="404",
 *            message="You have`n free time. for request period."
 *          ),
 *			@SWG\ResponseMessage(
 *            code="410",
 *            message="No one found."
 *          ),
 *			@SWG\ResponseMessage(
 *            code="414",
 *            message="Bad time."
 *          ),
 *           @SWG\ResponseMessage(
 *            code="500",
 *            message="Server side problem."
 *          )
 *       ),
 * @SWG\Parameter(
 *           name="json",
 *           description="json",
 *           paramType="body",
 *           required=true,
 *           allowMultiple="false",
 *           type="FindPeopleParams"
 *         )
 *     )
 *   )
 * )
 */
class FindPeople extends Common {

    /** @var int $q_s Query date start */
    /** @var int $q_e Query date end */
    var $q_s;
    var $q_e;

    /** @var int $t_s Query time start */
    /** @var int $t_e Query time end */
    var $t_s;
    var $t_e;

    /** @var int $goal Query goal */
    /** @var int $industry Query industry */
    /** @var float $n_lat Query city square */
    /** @var float $s_lat Query city square */
    /** @var float $n_lng Query city square */
    /** @var float $s_lng Query city square */
    var $goal;
    var $industry;
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

    private function getCity($city) {
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

    public function getValues($data) {
        $che = true;

        // Get all params
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["data_from"])) $this->q_s = $data["data_from"]; else $che = false;
        if (isset($data["data_to"])) $this->q_e = $data["data_to"]; else $che = false;
        if (isset($data["time_from"])) $this->t_s = $data["time_from"]; else $che = false;
        if (isset($data["time_to"])) $this->t_e = $data["time_to"]; else $che = false;
        if (isset($data["city"])) $city = $data["city"]; else $che = false;
        if (isset($data["offset"])) $this->offset = $data["offset"]; else $che = false;

        if (isset($data["goal"])) $this->goal = $data["goal"];
        if (isset($data["industry"])) $this->industry = $data["industry"];

        if (!$che) $this->answer('Not all params given',400);

        // Check params
        if ( $token != null && $token != '' && is_numeric($this->t_s) && is_numeric($this->t_e) && is_numeric($this->q_s) && is_numeric($this->q_e) &&  $city != null && $city != '') {
            $this->getUser($token);
            $city = $this->getCity($city);
            if ($city) {
                $this->free = $this->checkFree();
//                ~rt($this->free);
                $this->n_lat = $city['n_lat'];
                $this->s_lat = $city['s_lat'];
                $this->n_lng = $city['n_lng'];
                $this->s_lng = $city['s_lng'];

                $this->goal = ($this->goal) ? "and c.goal = $this->goal":' ';
                $this->industry = ($this->industry) ? "and u.industry_id = $this->industry":' ';
            }
            else
                $this->answer('Bad city',400);
        }
        else
            $this->answer('Not all params given',400);
    }

    public function findUsers() {
        $result = array();
        foreach ($this->free as $row) {
            $this->q_s = $row['start_time'];
            $this->q_e = $row['end_time'];
            $find = $this->query("
                select c.id as id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time)) as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time
                from free_slots c
                left join users u on c.user_id = u.id
                where
                ((unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.start_time) <= '$this->q_e')
                or (unix_timestamp(c.end_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) <= '$this->q_s' and unix_timestamp(c.end_time) >= '$this->q_e'))
                and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
                and u.status = 0
                and u.id != $this->user_id
                $this->industry
                $this->goal
                having ( end_time - start_time ) >= 3600
            ",false,true);
            $result = array_merge($result,$find);
        }
        shuffle($result);
        return $result;
    }

}