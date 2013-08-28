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

    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     *
     * @SWG\Model(id="addMeetingsParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="date_from",type="timestamp")
     * @SWG\Property(name="date_to",type="timestamp")
     * @SWG\Property(name="city",type="string")
     * @SWG\Property(name="foursquare_id",type="string")
     * @SWG\Property(name="goal",type="string")
     * @SWG\Property(name="person",type="string")
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
     *            reason="You have meeting on this time."
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


    /**
     *
     * @SWG\Model(id="getFreeSlotsParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="user_id",type="int")
     *
     *
     * @SWG\Api(
     *   path="/meetings/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
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
    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $body = $this->getRequest()->getRawBody();
        $data = @Zend_Json::decode($body);
        if (isset($data['private_key']) && $data['private_key'] != null && $data['private_key'] != '' && isset($data['user_id']) && is_numeric($data['user_id']) ) {
            $user = Application_Model_DbTable_Users::getUserData($data['private_key']);
            if ($user) {
                $db = new Application_Model_DbTable_Calendar();
                $res = $db->getFreeSlots($user,$data['user_id']);

                $this->_helper->json->sendJson(array(
                    'body' => $res['body'],
                    'errorCode' => $res['code']
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