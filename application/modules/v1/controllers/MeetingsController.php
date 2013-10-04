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
        if (isset($data['key']) && $data['key'] && isset($data['sid']) && is_numeric($data['sid']) && isset($data['answer']) && is_numeric($data['answer']) ) {
            if ($user = Application_Model_DbTable_EmailUsers::getUser($data['key'])) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => (new Application_Model_DbTable_Calendar())->answerMeetingEmail($user,$data['sid'],$data['answer'])
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
     *          ),
     *           @SWG\ErrorResponse(
     *            code="301",
     *            reason="You have meeting with this user on this time."
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
        $data = Zend_Json::decode($body);

        if (!isset($data['offset'])) $data['offset'] = 0;

        if (isset($data['private_key']) && $data['private_key'] && isset($data['person']) && is_numeric($data['person']) && isset($data['city']) && $data['city'] && isset($data['date_from']) && is_numeric($data['date_from']) && isset($data['date_to']) && is_numeric($data['date_to'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);

            $validators = array(
                '*' => array()
            );
            $filters = array(
                'goal' => array('StringTrim','HtmlEntities','Int'),
                'foursquare_id' => array('StringTrim','HtmlEntities'),
                'city' => array('StringTrim','HtmlEntities'),
                'person_value' => array('StringTrim','HtmlEntities'),
                'person_name' => array('StringTrim','HtmlEntities'),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);


            $userData = array(
                'person' => $data['person'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'goal' => $input->getEscaped('goal'),
                'foursquare_id' => $input->getEscaped('foursquare_id'),
                'city' => $input->getEscaped('city'),
                'person_value' => $input->getEscaped('person_value'),
                'person_name' => $input->getEscaped('person_name'),
            );

            $db = new Application_Model_DbTable_Calendar();
            if ($data['person'] == 0) {
                $res = $db->createFreeSlot($user,$userData);
            }
            elseif ($data['person'] == 1) {
                $res = $db->createMeeting($user,$userData);
            }
            elseif ($data['person'] == 2) {
                $res = $db->createMeetingEmail($user,$userData);
            }
            else {
                $res = 400;
            }

            if (isset($res['id'])) {
                $this->_helper->json->sendJson(array(
                    'body' => $res,
                    'errorCode' => 200
                ));
            }
            elseif (isset($res['body'])) {
                $this->_helper->json->sendJson(array(
                    'body' => $res['body'],
                    'errorCode' => $res['error']
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

    /**
     *
     * @SWG\Model(id="answerMeetingsParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="status",type="int")
     * @SWG\Property(name="foursqure_id",type="string")
     * @SWG\Property(name="start_time",type="string")
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
        $data = Zend_Json::decode($body);
        if (isset($data['private_key']) && $data['private_key'] && isset($data['id']) && is_numeric($data['id']) && isset($data['status']) && is_numeric($data['status']) ) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);
            $validators = array(
                '*' => array()
            );
            $filters = array(
                'foursquare_id' => array('StringTrim','HtmlEntities'),
                'start_time' => array('StringTrim','HtmlEntities','Int'),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);

            $db = new Application_Model_DbTable_Calendar();
            $res = $db->answerMeeting($user,$data['id'],$data['status'],$input->getEscaped('foursquare_id'),$input->getEscaped('start_time'));

            if (isset($res['body'])) {
                $this->_helper->json->sendJson(array(
                    'body' => $res['body'],
                    'errorCode' => $res['error']
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