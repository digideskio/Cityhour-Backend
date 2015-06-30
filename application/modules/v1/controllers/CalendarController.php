<?php


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
     *       method="GET",
     *       summary="Get calendar.",
     *       type="void",
     *       nickname="GetCalendar",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="407",
     *            message="You blocked."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required=true,
     *           allowMultiple="false",
     *           type="string"
     *         ),
     * @SWG\Parameter(
     *           name="id",
     *           description="id",
     *           paramType="query",
     *           required=true,
     *           allowMultiple="false",
     *           type="string"
     *         )
     *     )
     *   )
     * )
     */
    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $user = Application_Model_DbTable_Users::authorize($this->_request->getParam('private_key'));

        $this->_helper->json->sendJson(array(
            'body' => (new Application_Model_DbTable_Calendar())->getAll($user,$this->_request->getParam('id')),
            'errorCode' => '200'
        ));

    }

    /**
     *
     * @SWG\Model(id="slotCalendarParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="slots",type="array",items="$ref:creteSlotParams")
     * @SWG\Property(name="calendars",type="json")
     *
     * @SWG\Model(id="creteSlotParams")
     * @SWG\Property(name="start_time",type="timestamp")
     * @SWG\Property(name="end_time",type="timestamp")
     * @SWG\Property(name="hash",type="string")
     * @SWG\Property(name="calendar_name",type="string")
     *
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="Add busy time slot to calendar.",
     *       type="void",
     *       nickname="slotCalendar",
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
     *          @SWG\ResponseMessage(
     *            code="407",
     *            message="You blocked."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="slotCalendarParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['calendars']) && isset($data['slots'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);
            $db = new Application_Model_DbTable_Calendar();
            if ($db->addSlots($data,$user)) {
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
                'errorCode' => '400'
            ));
        }
    }

    /**
     *
     * @SWG\Model(id="slotCalendarUpdateParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="integer")
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
     *       method="PUT",
     *       summary="Update time slot.",
     *       type="void",
     *       nickname="UpdateCalendar",
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
     *            code="404",
     *            message="Slot not found or U have`n right to edit it."
     *          ),
     *           @SWG\ResponseMessage(
     *            code="408",
     *            message="User for meet blocked."
     *          ),
     *           @SWG\ResponseMessage(
     *            code="407",
     *            message="User blocked."
     *          ),
     *           @SWG\ResponseMessage(
     *            code="300",
     *            message="Request user busy."
     *          ),
     *           @SWG\ResponseMessage(
     *            code="301",
     *            message="You have meeting with this user on this time."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="slotCalendarUpdateParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['id']) && is_numeric($data['id'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);

            $db = new Application_Model_DbTable_Calendar();
            if ($res = $db->getSlot($data['id'],$user['id'],false,false,true)) {
                if ($res['type'] == 2 && isset($data['rating']) && is_numeric($data['rating']) ) {
                    $res = $db->updateRating($data,$res);
                    if (isset($res['id'])) {
                        $this->_helper->json->sendJson(array(
                            'body' => $res,
                            'errorCode' => 200
                        ));
                    }
                    else {
                        $this->_helper->json->sendJson(array(
                            'errorCode' => $res
                        ));
                    }
                }
                elseif (isset($data['date_from']) && is_numeric($data['date_from']) && isset($data['date_to']) && is_numeric($data['date_to']) && isset($data['person']) && is_numeric($data['person'])) {
                    $validators = array(
                        '*' => array()
                    );
                    $filters = array(
                        'goal' => array('StringTrim','HtmlEntities','Int'),
                        'offset' => array('StringTrim','HtmlEntities','Int'),
                        'foursquare_id' => array('StringTrim','HtmlEntities'),
                        'city' => array('StringTrim','HtmlEntities'),
                        'person_value' => array('StringTrim','HtmlEntities'),
                        'person_name' => array('StringTrim','HtmlEntities'),
                    );
                    $input = new Zend_Filter_Input($filters, $validators, $data);

                    $userData = array(
                        'id' => $data['id'],
                        'date_from' => $data['date_from'],
                        'date_to' => $data['date_to'],
                        'person' => $data['person'],
                        'goal' => $input->getEscaped('goal'),
                        'offset' => $input->getEscaped('offset'),
                        'foursquare_id' => $input->getEscaped('foursquare_id'),
                        'city' => $input->getEscaped('city'),
                        'person_value' => $input->getEscaped('person_value'),
                        'person_name' => $input->getEscaped('person_name'),
                    );

                    $res = $db->updateSlot($userData,$res,$user);
                    if (isset($res['id'])) {
                        $this->_helper->json->sendJson(array(
                            'body' => $res,
                            'errorCode' => 200
                        ));
                    }
                    else {
                        $this->_helper->json->sendJson(array(
                            'errorCode' => $res
                        ));
                    }
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
     *       method="DELETE",
     *       summary="Cancel time slot.",
     *       type="void",
     *       nickname="CancelTimeSlot",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="400",
     *            message="Not all params given."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="404",
     *            message="Not found slot that you can cancel."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="407",
     *            message="You blocked."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required=true,
     *           allowMultiple="false",
     *           type="string"
     *         ),
     * @SWG\Parameter(
     *           name="id",
     *           description="id",
     *           paramType="query",
     *           required=true,
     *           allowMultiple="false",
     *           type="integer"
     *         )
     *     )
     *   )
     * )
     */
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $token = $this->_request->getParam('private_key');
        $id = $this->_request->getParam('id');
        if ($token && $id) {
            $user = Application_Model_DbTable_Users::authorize($token);
            $this->_helper->json->sendJson(array(
                'errorCode' => (new Application_Model_DbTable_Calendar())->deleteSlot($user,$id)
            ));
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

