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
 * @SWG\Model(id="SuggestParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="debug",type="boolean")
 *
 * @SWG\Property(name="data_from",type="timestamp")
 * @SWG\Property(name="data_to",type="timestamp")
 * @SWG\Property(name="lat",type="float")
 * @SWG\Property(name="lng",type="float")
 *
 *
 *
 * @SWG\Api(
 *   path="/suggest.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       httpMethod="POST",
 *       summary="Suggest people.",
 *       responseClass="void",
 *       nickname="Suggest",
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
 *           dataType="SuggestParams"
 *         )
 *     )
 *   )
 * )
 */
class Suggest extends Common {

    /** @var int $q_s Query time start */
    /** @var int $q_e Query time end */
    var $q_s;
    var $q_e;

    /** @var float $lat Query lat */
    /** @var float $lng Query lng */
    var $lat;
    var $lng;

    /** @var int $user_id Id of user */
    var $user_id;


    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
    }

    public function getValues($data) {
        $che = true;

        // Get all params
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["lat"])) $this->lat = $data["lat"]; else $che = false;
        if (isset($data["lng"])) $this->lng = $data["lng"]; else $che = false;
        if (isset($data["data_from"])) $this->q_s = $data["data_from"]; else $che = false;
        if (isset($data["data_to"])) $this->q_e = $data["data_to"]; else $che = false;

        if (!$che) $this->answer('Not all params given',400);

        // Check params
        if ( $token != null && $token != '' && is_numeric($this->lat) && is_numeric($this->lng) && is_numeric($this->q_s) && is_numeric($this->q_e)) {
            $this->getUser($token);
            $this->free = $this->checkFree();
            if (!$this->free) $this->answer('You have`n free time. for request period.',404);
        }
        else
            $this->answer('Not all params given',400);
    }

    public function findUsers() {

        return $this->query("
            select c.id as id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time)) as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time
            from free_slots c
            left join users u on c.user_id = u.id
            where
            ((unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e'))
			and u.status = 0
			and u.id != $this->user_id
           having ( end_time - start_time ) > 3600
           ORDER BY acos(sin($this->lat) * sin(c.lat) + cos($this->lat) * cos(c.lat) * cos(c.lng - ($this->lng))) asc
        ",false,true);
    }

}