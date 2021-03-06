<?php


/**
 * @SWG\Resource(
 *  resourcePath="/helpers"
 * )
 */

require 'Common.class.php';


/**
 *
 * @SWG\Model(id="SuggestParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="debug",type="boolean")
 *
 * @SWG\Property(name="offset",type="integer")
 * @SWG\Property(name="data_from",type="timestamp")
 * @SWG\Property(name="data_to",type="timestamp")
 * @SWG\Property(name="time_from",type="timestamp")
 * @SWG\Property(name="time_to",type="timestamp")
 * @SWG\Property(name="lat",type="float")
 * @SWG\Property(name="lng",type="float")
 *
 *
 *
 * @SWG\Api(
 *   path="/suggest.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       method="POST",
 *       summary="Suggest people.",
 *       type="void",
 *       nickname="Suggest",
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
 *           type="SuggestParams"
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

    /** @var int $t_s Query time start */
    /** @var int $t_e Query time end */
    var $t_s;
    var $t_e;

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
        if (isset($data["time_from"])) $this->t_s = $data["time_from"]; else $che = false;
        if (isset($data["time_to"])) $this->t_e = $data["time_to"]; else $che = false;
        if (isset($data["offset"])) $this->offset = $data["offset"]; else $che = false;

        if (!$che) $this->answer('Not all params given',400);

        // Check params
        if ( $token != null && $token != '' && is_numeric($this->t_s) && is_numeric($this->t_e) && is_numeric($this->lat) && is_numeric($this->lng) && is_numeric($this->q_s) && is_numeric($this->q_e)) {
            $this->getUser($token);
            $this->free = $this->checkFree(true);

            if (!$this->free) {
                return false;
            }
            else {
                return true;
            }
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
                select c.id as id, c.user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time)) as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, (3959 * acos(cos(radians($this->lat)) * cos(radians(lat)) * cos( radians(lng) - radians($this->lng)) + sin(radians($this->lat)) * sin(radians(lat)))) as dist
                from free_slots c
                left join users u on c.user_id = u.id
                where
                ((unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.start_time) <= '$this->q_e')
                or (unix_timestamp(c.end_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                or (unix_timestamp(c.start_time) <= '$this->q_s' and unix_timestamp(c.end_time) >= '$this->q_e'))
                and u.status = 0
                and u.id != $this->user_id
               having ( end_time - start_time ) >= 3600
               ORDER BY dist asc
            ",false,true);
            $result = array_merge($result,$find);
        }
        $result = $this->sortByDist($result);
        return $result;
    }

}