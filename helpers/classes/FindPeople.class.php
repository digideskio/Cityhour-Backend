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
 *           dataType="FindPeopleParams"
 *         )
 *     )
 *   )
 * )
 */
class FindPeople extends Common {

    /** @var int $q_s Query time start */
    /** @var int $q_e Query time end */
    var $q_s;
    var $q_e;

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

    /** @var array $data Income paramms */
    var $data;

    /** @var int $user_id Id of user */
    var $user_id;


    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
    }

    public function getValues($data) {
        $this->data = $data;
        $che = true;

        // Get all params
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["data_from"])) $this->q_s = $data["data_from"]; else $che = false;
        if (isset($data["data_to"])) $this->q_e = $data["data_to"]; else $che = false;
        if (isset($data["city"])) $city = $data["city"]; else $che = false;

        if (isset($data["goal"])) $this->goal = $data["goal"];
        if (isset($data["industry"])) $this->industry = $data["industry"];

        if (!$che) $this->answer('Not all params given',400);

        // Check params
        if ( $token != null && $token != '' && is_numeric($this->q_s) && is_numeric($this->q_e) &&  $city != null && $city != '') {
            $this->getUser($token);
            $city = $this->getCity($city);
            if ($city) {
                if ($this->free = $this->checkFree()) {
                    $this->n_lat = $city['n_lat'];
                    $this->s_lat = $city['s_lat'];
                    $this->n_lng = $city['n_lng'];
                    $this->s_lng = $city['s_lng'];

                    $this->goal = ($this->goal) ? "and c.goal = $this->goal":' ';
                    $this->industry = ($this->industry) ? "and u.industry_id = $this->industry":' ';
                }
                else
                    $this->answer('You have`n free time. for request period.',404);
            }
            else
                $this->answer('Bad city',400);
        }
        else
            $this->answer('Not all params given',400);
    }

    public function findUsers() {
        return $this->query("
            select c.id as id, c.user_id, c.start_time, c.end_time
            from free_slots c
            left join users u on c.user_id = u.id
            where
            ((unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e'))
            and c.lng BETWEEN $this->s_lng AND $this->n_lng AND c.lat BETWEEN $this->s_lat AND $this->n_lat
			and u.status = 0
            $this->industry
            $this->goal
			having ( Unix_timestamp(end_time) - Unix_timestamp(start_time) ) > 3600
        ",false,true);
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

}