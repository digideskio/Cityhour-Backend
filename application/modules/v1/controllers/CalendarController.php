<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/calendar"
 * )
 */

class V1_CalendarController extends Zend_Rest_Controller
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $this->getAction();
    }

    /**
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Get calendar.",
     *       responseClass="void",
     *       nickname="GetCalendar",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Authentication failed."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params given."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="string"
     *         )
     *     )
     *   )
     * )
     */
    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $token = $this->_request->getParam('private_key');
        if ($token && $token != null && $token != '') {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $res = $db->getAll($user);
                $this->_helper->json->sendJson(array(
                    'body' => $res,
                    'errorCode' => '200'
                ));
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '401'
                ));
            }
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => '400'
            ));
        }

    }

    /**
     *
     * @SWG\Model(id="slotCalendarParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="slots",type="Array",items="$ref:creteSlotParams")
     *
     * @SWG\Model(id="creteSlotParams")
     * @SWG\Property(name="user_id_second",type="string")
     * @SWG\Property(name="start_time",type="timestamp")
     * @SWG\Property(name="end_time",type="timestamp")
     * @SWG\Property(name="foursquare_id",type="string")
     * @SWG\Property(name="goal",type="int")
     * @SWG\Property(name="city",type="string")
     * @SWG\Property(name="hash",type="string")
     * @SWG\Property(name="calendar_name",type="string")
     * @SWG\Property(name="status",type="int")
     *
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Add busy time slot to calendar.",
     *       responseClass="void",
     *       nickname="slotCalendar",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params correct."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="401",
     *            reason="Have no permissions."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="slotCalendarParams"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $body = $this->getRequest()->getRawBody();
        $data = Zend_Json::decode($body);
        if (isset($data['private_key'])) $token = $data['private_key']; else $token = false;
        if ($token && $token != null && $token != '' && isset($data['calendars']) && isset($data['slots'])) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $res = $db->addSlots($data,$user);
                if ($res) {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '200'
                    ));
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '400'
                    ));
                }
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '401'
                ));
            }
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => '400'
            ));
        }
    }

    /**
     *
     * @SWG\Model(id="slotCalendarUpdateParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="date_from",type="timestamp")
     * @SWG\Property(name="date_to",type="timestamp")
     * @SWG\Property(name="city",type="string")
     * @SWG\Property(name="foursquare_id",type="string")
     * @SWG\Property(name="goal",type="string")
     * @SWG\Property(name="rating",type="string")
     * @SWG\Property(name="person",type="string")
     * @SWG\Property(name="person_value",type="string")
     * @SWG\Property(name="person_name",type="string")
     *
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
     *       summary="Update time slot.",
     *       responseClass="void",
     *       nickname="UpdateCalendar",
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
     *            code="404",
     *            reason="Slot not found or U have`n right to edit it."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="408",
     *            reason="User for meet blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="407",
     *            reason="User blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="300",
     *            reason="Request user busy."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="slotCalendarUpdateParams"
     *         )
     *     )
     *   )
     * )
     */
    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $body = $this->getRequest()->getRawBody();
        $data = Zend_Json::decode($body);
        if (isset($data['private_key']) && $data['private_key'] != null && $data['private_key'] != '' && isset($data['id']) && is_numeric($data['id']) ) {
            $user = Application_Model_DbTable_Users::getUserData($data['private_key']);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $res = $db->getSlot($data['id'],$user['id']);

                if ($res) {
                    $res = $db->updateSlot($data,$res,$user);
                    if (is_numeric($res)) {
                        $this->_helper->json->sendJson(array(
                            'errorCode' => $res
                        ));
                    }
                    else {
                        $this->_helper->json->sendJson(array(
                            'errorCode' => '400'
                        ));
                    }
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '404'
                    ));
                }
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '401'
                ));
            }
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => '400'
            ));
        }
    }

    /**
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="DELETE",
     *       summary="Cancel time slot.",
     *       responseClass="void",
     *       nickname="CancelTimeSlot",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Authentication failed."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params given."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="404",
     *            reason="Not found slot that you can cancel."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="string"
     *         ),
     * @SWG\Parameter(
     *           name="id",
     *           description="id",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="int"
     *         )
     *     )
     *   )
     * )
     */
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(200);

        $token = $this->getParam('private_key');
        $id = $this->getParam('id');

        if ($token && $token != null && $token != '' && is_numeric($id)) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $res = $db->deleteSlot($user,$id);
                $this->_helper->json->sendJson(array(
                    'errorCode' => $res
                ));
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '401'
                ));
            }
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => '400'
            ));
        }
    }

    public function headAction()
    {
        $this->getResponse()->setBody(null);
    }

    public function optionsAction()
    {
        $this->getResponse()->setBody(null);
        $this->getResponse()->setHeader('Allow', 'OPTIONS, HEAD, INDEX, GET, POST, PUT, DELETE');
    }

}

