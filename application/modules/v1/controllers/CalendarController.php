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
     * @SWG\Property(name="start_time",type="timestamp")
     * @SWG\Property(name="end_time",type="timestamp")
     * @SWG\Property(name="foursquare_id",type="int")
     * @SWG\Property(name="goal",type="int")
     * @SWG\Property(name="city",type="string")
     *
     *
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Add time slot to calendar.",
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
        if ($token && $token != null && $token != '') {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $data['user_id'] = $user['id'];
                $data['start_time'] = date('Y-m-d H:i:s',$data['start_time']);
                $data['end_time'] = date('Y-m-d H:i:s',$data['end_time']);
//                if (isset($data['city'])) {
//                    $config = $this->getInvokeArg('bootstrap')->getOption('google');
//                    $url = $config['url'].$data['city'];
//                    $client = new Zend_Http_Client($url);
//                    $req = json_decode($client->request()->getBody(), true);
//                    $data['city_name'] = $req['result']['address_components'][0]['short_name'];
//                    $data['lat'] = $req['result']['geometry']['location']['lat'];
//                    $data['lng'] = $req['result']['geometry']['location']['lat'];
//                }
                $db = new Application_Model_DbTable_Calendar();
                unset($data['private_key']);
                $db->addSlot($data);
                $this->_helper->json->sendJson(array(
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
     * @SWG\Model(id="slotCalendarUpdateParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="city",type="string")
     * @SWG\Property(name="start_time",type="timestamp")
     * @SWG\Property(name="end_time",type="timestamp")
     * @SWG\Property(name="foursquare_id",type="int")
     * @SWG\Property(name="goal",type="int")
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
        if (isset($data['private_key'])) $token = $data['private_key']; else $token = false;
        if ($token && $token != null && $token != '' && is_numeric($data['id'])) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                if (isset($data['start_time'])) {
                    $data['start_time'] = date('Y-m-d H:i:s',$data['start_time']);
                }
                if (isset($data['end_time'])) {
                    $data['end_time'] = date('Y-m-d H:i:s',$data['end_time']);
                }
//                if (isset($data['city'])) {
//                    $config = $this->getInvokeArg('bootstrap')->getOption('google');
//                    $url = $config['url'].$data['city'];
//                    $client = new Zend_Http_Client($url);
//                    $req = json_decode($client->request()->getBody(), true);
//                    $data['city'] = $req['result']['address_components'][0]['short_name'];
//                    $data['lat'] = $req['result']['geometry']['location']['lat'];
//                    $data['lng'] = $req['result']['geometry']['location']['lat'];
//                }
                unset($data['private_key']);
                $db = new Application_Model_DbTable_Calendar();
                $db->updateSlot($user,$data,$data['id']);
                $this->_helper->json->sendJson(array(
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
     * @SWG\Api(
     *   path="/calendar/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="DELETE",
     *       summary="Delete time slot.",
     *       responseClass="void",
     *       nickname="DeleteTimeSlot",
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

