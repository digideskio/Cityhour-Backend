<?php

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


$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
require 'classes/Common.class.php';
$cls = new Common($debug);

$che = true;
if (isset($data["private_key"])) $token = $data["private_key"]; else $che = false;
if (isset($data["data"])) $ids = $data["data"]; else $che = false;
if (isset($data["data_from"])) $cls->q_s = $data["data_from"]; else $che = false;
if (isset($data["data_to"])) $cls->q_e = $data["data_to"]; else $che = false;
if (isset($data["time_from"])) $cls->t_s = $data["time_from"]; else $che = false;
if (isset($data["time_to"])) $cls->t_e = $data["time_to"]; else $che = false;
if (isset($data["offset"])) $cls->offset = $data["offset"]; else $che = false;

if (!$che) $cls->answer('Not all params given',400);

$cls->start();
$cls->connect();
$cls->getUser($data['private_key']);
$slots = array();
$first = array();
$users = array();
$i = 0;

foreach ($ids as $row) {
    if ($i < 25) {
        array_push($first,$row['id']);
    }
    else {
        array_push($slots,$row);
    }

    if (!in_array($row['user_id'],$users)) {
        $i++;
        array_push($users,$row['user_id']);
    }
}

if ($first) {
    $cls->getFullTime();
    $cls->answer(array(
        'users' => $cls->getUsers($first),
        'data' => $slots
    ),200);
}
else {
    $cls->answer('Not all params given',410);
}