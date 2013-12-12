<?php

require 'Common.class.php';

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/helpers"
 * )
 */

/**
 *
 * @SWG\Model(id="GetUsersParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="data",type="Array")
 *
 * @SWG\Property(name="offset",type="int")
 * @SWG\Property(name="data_from",type="timestamp")
 * @SWG\Property(name="data_to",type="timestamp")
 * @SWG\Property(name="time_from",type="timestamp")
 * @SWG\Property(name="time_to",type="timestamp")
 *
 *
 * @SWG\Api(
 *   path="/users.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       httpMethod="POST",
 *       summary="Get Users.",
 *       responseClass="void",
 *       nickname="GetUsers",
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
 *           dataType="GetUsersParams"
 *         )
 *     )
 *   )
 * )
 */
class Users extends Common {

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

    /** @var array $ids Data about user */
    var $ids;


    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
    }

    public function getValues($data) {
        $che = true;
        if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
        if (isset($data["data"])) $this->ids = $data["data"]; else $che = false;
        if (isset($data["data_from"])) $this->q_s = $data["data_from"]; else $che = false;
        if (isset($data["data_to"])) $this->q_e = $data["data_to"]; else $che = false;
        if (isset($data["time_from"])) $this->t_s = $data["time_from"]; else $che = false;
        if (isset($data["time_to"])) $this->t_e = $data["time_to"]; else $che = false;
        if (isset($data["offset"])) $this->offset = $data["offset"]; else $che = false;
        if (isset($data["lat"])) $this->lat = $data["lat"];
        if (isset($data["lng"])) $this->lng = $data["lng"];

        if (!$che) $this->answer('Not all params given',400);

        if ( $token && $this->ids && is_numeric($this->t_s) && is_numeric($this->t_e)  && is_numeric($this->q_s) && is_numeric($this->q_e)) {
            $this->getUser($token);
            $this->free = $this->checkFree();
        }
    }

    public function getMoreTime() {
        $now = time();
        $f = $now + $this->offset;
        $e = $now + 172800 + $this->offset;
        $this->q_s = $f;
        $this->t_s = $f;
        $this->q_e = $e;
        $this->t_e  = $e;
        $this->free = $this->checkFree();
    }


}