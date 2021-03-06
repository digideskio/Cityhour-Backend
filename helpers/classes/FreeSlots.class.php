<?php


/**
 * @SWG\Resource(
 *  resourcePath="/helpers"
 * )
 */

require 'Common.class.php';


/**
 *
 * @SWG\Model(id="getFreeSlotsParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="user_id",type="integer")
 * @SWG\Property(name="debug",type="boolean")
 *
 *
 * @SWG\Api(
 *   path="/freeSlots.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       method="POST",
 *       summary="Get Free Slots.",
 *       type="void",
 *       nickname="getFreeSlots",
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
 *            code="408",
 *            message="Request user blocked."
 *          ),
 *           @SWG\ResponseMessage(
 *            code="407",
 *            message="Current user blocked."
 *          )
 *       ),
 * @SWG\Parameter(
 *           name="json",
 *           description="json",
 *           paramType="body",
 *           required=true,
 *           allowMultiple="false",
 *           type="getFreeSlotsParams"
 *         )
 *     )
 *   )
 * )
 */
class FreeSlots extends Common {

    /** @var int $n_id request user_id */
    var $n_id;

    /** @var int $q_s Query time start */
    /** @var int $q_e Query time end */
    var $q_s;
    var $q_e;

    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
    }

    public function getValues($data) {
        $che = true;
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["user_id"])) $this->n_id = $data["user_id"]; else $che = false;

        if (!$che) {
            $this->answer('Not all params given',400);
        }

        if ( $token && is_numeric($this->n_id)) {
            $this->getUser($token);
            $this->q_s = time();
            $this->q_e = strtotime('+16 days', $this->q_s);
        }
        else {
            $this->answer('Not all params given',400);
        }
    }

    public function getFree() {
        $rSlots =array();
        $freeSlots = $this->query("
                select c.id, 1 as user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time))  as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, c.type, c.lat, c.lng, c.goal, c.offset, c.foursquare_id, c.place, c.city, c.city_name
                from free_slots c
                where
                (
                    (unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                )
                and c.type = 1
                and c.user_id = $this->n_id
            ",false,true);
        if ($freeSlots) {
            $meet = $this->query("
                select c.id, 1 as user_id, GREATEST('$this->q_s', unix_timestamp(c.start_time))  as start_time, LEAST('$this->q_e', unix_timestamp(c.end_time)) as end_time, c.type, c.lat, c.lng, c.goal, c.offset, c.foursquare_id, c.place, c.city, c.city_name
                from calendar c
                where
                (
                    (unix_timestamp(c.start_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.end_time) between '$this->q_s' and '$this->q_e') or (unix_timestamp(c.start_time) >= '$this->q_s' and unix_timestamp(c.end_time) <= '$this->q_e')
                )
                and c.type = 2
                and c.status = 2
                and c.user_id = $this->user_id
            ",false,true);

            $AllData = array_merge($freeSlots,$meet);
            // Free slots not cross Meet slots
            $rSlots = $this->findCrossOrNot($AllData,1,2,false);

            // Free slots cross Meet slots
            $slots = $this->findCrossOrNot($AllData,1,2,true);
            $slots = $this->sortById($slots);
            $rSlots = array_merge($rSlots,$this->addOrRemoveTime($slots,false));
            $rSlots = $this->getMoreThanHour($rSlots);
            return $rSlots;
        }
        else {
            return $rSlots;
        }
    }


}