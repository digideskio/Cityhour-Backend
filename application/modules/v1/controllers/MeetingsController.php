<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/meetings"
 * )
 */

class V1_MeetingsController extends Zend_Rest_Controller
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
     *   path="/meetings/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Answer handler for email meeting request.",
     *       responseClass="void",
     *       nickname="AcceptEmailMeeting",
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
     *            reason="User for meet blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="301",
     *            reason="Request user have meeting on this time."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="answer",
     *           description="answer",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="int"
     *         ),
     * @SWG\Parameter(
     *           name="sid",
     *           description="Meeting id",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="int"
     *         ),
     * @SWG\Parameter(
     *           name="key",
     *           description="User key",
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
        $data = $this->_request->getParams();
        if (isset($data['key']) && $data['key'] != null && $data['key'] != '' && isset($data['sid']) && is_numeric($data['sid']) && isset($data['answer']) && is_numeric($data['answer']) ) {
            $user = Application_Model_DbTable_EmailUsers::getUser($data['key']);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $this->_helper->json->sendJson(array(
                    'errorCode' => $db->answerMeetingEmail($user,$data['sid'],$data['answer'])
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
     * @SWG\Model(id="addMeetingsParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="date_from",type="timestamp")
     * @SWG\Property(name="date_to",type="timestamp")
     * @SWG\Property(name="city",type="string")
     * @SWG\Property(name="foursquare_id",type="string")
     * @SWG\Property(name="goal",type="int")
     * @SWG\Property(name="person",type="int")
     * @SWG\Property(name="person_value",type="string")
     * @SWG\Property(name="person_name",type="string")
     *
     *
     * @SWG\Api(
     *   path="/meetings/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Add meeting or free slot.",
     *       responseClass="void",
     *       nickname="addMeeting",
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
     *            reason="User for meet blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="407",
     *            reason="User blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="300",
     *            reason="You have meeting or free slot on this time."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="addMeetingsParams"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $body = $this->getRequest()->getRawBody();
        $data = @Zend_Json::decode($body);
        if (isset($data['private_key']) && $data['private_key'] != null && $data['private_key'] != '' && isset($data['person']) && is_numeric($data['person']) && isset($data['city']) && $data['city'] != null && $data['city'] != '' && isset($data['date_from']) && is_numeric($data['date_from']) && isset($data['date_to']) && is_numeric($data['date_to'])) {
            $user = Application_Model_DbTable_Users::getUserData($data['private_key']);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                if ($data['person'] == 0) {
                    $res = $db->createFreeSlot($user,$data);
                }
                elseif ($data['person'] == 1) {
                    $res = $db->createMeeting($user,$data);
                }
                elseif ($data['person'] == 2) {
                    $res = $db->createMeetingEmail($user,$data);
                }
                else {
                    $res = 400;
                }

                if ($res == 200) {
                    $res = $db->getAll($user);
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
     * @SWG\Model(id="answerMeetingsParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="status",type="int")
     * @SWG\Property(name="foursqure_id",type="string")
     *
     *
     * @SWG\Api(
     *   path="/meetings/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
     *       summary="Answer on meeting request.",
     *       responseClass="void",
     *       nickname="addMeeting",
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
     *            reason="User for meet blocked."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="407",
     *            reason="User blocked."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="404",
     *            reason="Right meeting not found."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="300",
     *            reason="You have meeting on this time."
     *          ),
     *           @SWG\ErrorResponse(
     *            code="301",
     *            reason="Request user have meeting on this time."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="answerMeetingsParams"
     *         )
     *     )
     *   )
     * )
     */
    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $body = $this->getRequest()->getRawBody();
        $data = @Zend_Json::decode($body);
        if (isset($data['private_key']) && $data['private_key'] != null && $data['private_key'] != '' && isset($data['id']) && is_numeric($data['id']) && isset($data['status']) && is_numeric($data['status']) ) {
            $user = Application_Model_DbTable_Users::getUserData($data['private_key']);
            if ($user) {
                if (isset($data['foursqure_id']) && $data['foursqure_id'] != null && $data['foursqure_id'] != '') {
                    $foursqure_id = $data['foursqure_id'];
                }
                else {
                    $foursqure_id = false;
                }

                $db = new Application_Model_DbTable_Calendar();
                $this->_helper->json->sendJson(array(
                    'errorCode' => $db->answerMeeting($user,$data['id'],$data['status'],$foursqure_id)
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

    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
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