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
 * @SWG\Model(id="getFreeSlotsParams")
 * @SWG\Property(name="private_key",type="string")
 * @SWG\Property(name="user_id",type="int")
 * @SWG\Property(name="debug",type="boolean")
 *
 *
 * @SWG\Api(
 *   path="/freeslots.php",
 *   @SWG\Operations(
 *     @SWG\Operation(
 *       httpMethod="POST",
 *       summary="Get Free Slots.",
 *       responseClass="void",
 *       nickname="getFreeSlots",
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
 *            code="408",
 *            reason="Request user blocked."
 *          ),
 *           @SWG\ErrorResponse(
 *            code="407",
 *            reason="Current user blocked."
 *          )
 *       ),
 * @SWG\Parameter(
 *           name="json",
 *           description="json",
 *           paramType="body",
 *           required="true",
 *           allowMultiple="false",
 *           dataType="getFreeSlotsParams"
 *         )
 *     )
 *   )
 * )
 */
class FreeSlots extends Common {

    /** @var int $n_id request user_id */
    var $n_id;

    public function __construct($debug,$data) {
        $this->debug = $debug;
        $this->data = $data;
        $this->mainWork();
    }

    public function mainWork() {
        $this->connect();
        $this->getValues();
        $res = $this->getFreeSlots();
        $this->answer($res,200);
    }

    public function getValues() {
        $che = true;
        if (isset($this->data["private_key"])) $token = $this->data["private_key"]; else $che = false;
        if (isset($this->data["user_id"])) $this->n_id = $this->data["user_id"]; else $che = false;

        if (!$che) {
            $this->answer('Not all params given',400);
            die();
        }

        if ( $token != null && $token != '' && is_numeric($this->n_id)) {
            $this->getUser($token);
        }
        else {
            $this->answer('Not all params given',400);
            die();
        }
    }

    public function getFreeSlots() {
        $this->query("
            create temporary table rSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");
        $this->query("
            insert into rSult (id, user_id, `type`, is_free, start_time, end_time)
            (
                select t.id, t.user_id, t.type, 0 as is_free, t.start_time, t.end_time
                from calendar t
                left outer JOIN calendar mt on t.user_id = mt.user_id
                and mt.type = 2
                and mt.status = 2
                and t.start_time <= mt.end_time
                and t.end_time >= mt.start_time

                where t.type = 1
                and t.user_id = $this->n_id
                and t.start_time >= now()
                and mt.id is null
            )
        ");
        $cross = $this->query("
            select t.id, t.user_id, t.type, 0 as is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from calendar t
            inner JOIN calendar mt on t.user_id = mt.user_id
            and mt.type = 2
            and mt.status = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            and t.user_id = $this->n_id
            and t.start_time >= now()
            order by t.id,mt.start_time
        ");
        $res = $this->getFreeArray($cross);
        $this->insertInto($res,'rSult');


        $this->query("
            create temporary table zSult (`id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `is_free` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL) ENGINE=MEMORY;
        ");
        $this->query("
            insert into zSult (id, user_id, `type`, is_free, start_time, end_time)
            (
                select t.id, t.user_id, t.type, 0 as is_free, t.start_time, t.end_time
                from rSult t
                left outer JOIN calendar mt on mt.user_id = $this->user_id
                and mt.type = 2
                and mt.status = 2
                and t.start_time <= mt.end_time
                and t.end_time >= mt.start_time

                where t.type = 1
                and t.user_id = $this->n_id
                and t.start_time >= now()
                and mt.id is null
            )
        ");
        $cross = $this->query("
            select t.id, t.user_id, t.type, 0 as is_free, UNIX_TIMESTAMP(t.start_time) AS start_time, UNIX_TIMESTAMP(t.end_time) as end_time,  UNIX_TIMESTAMP(mt.start_time) AS second_start_time, UNIX_TIMESTAMP(mt.end_time) as second_end_time
            from rSult t
            inner JOIN calendar mt on mt.user_id = $this->user_id
            and mt.type = 2
            and mt.status = 2
            and t.start_time <= mt.end_time
            and t.end_time >= mt.start_time

            where t.type = 1
            and t.user_id = $this->n_id
            and t.start_time >= now()
            order by t.id,mt.start_time
        ");
        $res = $this->getFreeArray($cross);
        $this->insertInto($res,'zSult');

        return $this->query("
            select r.id, r.start_time, r.end_time, c.foursquare_id, c.place, c.lat, c.lng
            from zSult r
            left join calendar c on r.id = c.id
            where (UNIX_TIMESTAMP(r.end_time) - UNIX_TIMESTAMP(r.start_time)) >= 3600
            order by r.start_time asc
        ",false,true);
    }


}