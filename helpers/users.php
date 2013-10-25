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


// $data = '
// {
//   "private_key": "c3077d5cd3efe0797fb516b3cb216b3d55242f425221f6a71b83c",
//   "debug": true,
//   "data": [{
//        "end_time": "1382922000",
//        "id": "295",
//        "start_time": "1382889600",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383339600",
//        "id": "296",
//        "start_time": "1383321600",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383094800",
//        "id": "297",
//        "start_time": "1383062400",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383267600",
//        "id": "299",
//        "start_time": "1383235200",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1382749200",
//        "id": "303",
//        "start_time": "1382716800",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383181200",
//        "id": "304",
//        "start_time": "1383148800",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383008400",
//        "id": "306",
//        "start_time": "1382976000",
//        "user_id": "1018"
//      },
//      {
//        "end_time": "1383145200",
//        "id": "345",
//        "start_time": "1383112800",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1383058800",
//        "id": "347",
//        "start_time": "1383026400",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382799600",
//        "id": "349",
//        "start_time": "1382767200",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1383231600",
//        "id": "350",
//        "start_time": "1383199200",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382713200",
//        "id": "354",
//        "start_time": "1382680800",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382886000",
//        "id": "356",
//        "start_time": "1382853600",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1383318000",
//        "id": "358",
//        "start_time": "1383285600",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382950800",
//        "id": "584",
//        "start_time": "1382940000",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382972400",
//        "id": "585",
//        "start_time": "1382951700",
//        "user_id": "1022"
//      },
//      {
//        "end_time": "1382972400",
//        "id": "379",
//        "start_time": "1382940000",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1382886000",
//        "id": "380",
//        "start_time": "1382853600",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1382713200",
//        "id": "381",
//        "start_time": "1382680800",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1383145200",
//        "id": "382",
//        "start_time": "1383112800",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1383318000",
//        "id": "387",
//        "start_time": "1383285600",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1383058800",
//        "id": "389",
//        "start_time": "1383026400",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1382799600",
//        "id": "390",
//        "start_time": "1382767200",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1383210000",
//        "id": "586",
//        "start_time": "1383199200",
//        "user_id": "1031"
//      },
//      {
//        "end_time": "1383231600",
//        "id": "587",
//        "start_time": "1383213600",
//        "user_id": "1031"
//      }
//    ]
// }
// ';

$data = file_get_contents("php://input");
$data = json_decode($data,true);
$debug = (isset($data["debug"]) && $data["debug"]) ? true:false;

if ($debug) require_once '../vendor/ref/ref.php';
include_once 'classes/Common.class.php';
$cls = new Common($debug);


if (isset($data['data']) && isset($data['private_key']) && $data['private_key']) {
    $ids = $data['data'];
}
else {
    $cls->answer('Not all params given',400);
}

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
    $cls->answer(array(
        'users' => $cls->getUsers($first),
        'date' => $slots
    ),200);
}
else {
    $cls->answer('Not all params given',410);
}