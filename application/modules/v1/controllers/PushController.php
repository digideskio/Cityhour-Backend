<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/push"
 * )
 */

class V1_PushController extends Zend_Rest_Controller
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
        $ids = $this->_request->getParam('ids');
        if ($ids && $ids != '' && $ids != ',') {
            $db = new Application_Model_DbTable_PushMessages();
            $res = $db->sendAll($ids);
        }
        else {
            $res = false;
        }

        if ($res) {
            $this->_helper->json->sendJson(array(
                'done' => true
            ));
        }
        else {
            $this->_helper->json->sendJson(array(
                'done' => false
            ));
        }
    }

    /**
     *
     * @SWG\Model(id="push")
     * @SWG\Property(name="token",type="string")
     * @SWG\Property(name="device",type="string")
     * @SWG\Property(name="debug",type="string")
     * @SWG\Property(name="private_key",type="string")
     *
     * @SWG\Api(
     *   path="/push/",
     * @SWG\Operations(
     * @SWG\Operation(
     *       nickname="AddPush",
     *       summary="Add push.",
     *       httpMethod="POST",
     *       responseClass="void",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Have no permissions"
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="push"
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
        if (isset($data['private_key'])) $private_key = $data['private_key']; else $private_key = false;
        if ($private_key && $private_key != null && $private_key != '') {
            $user = Application_Model_DbTable_Users::getUserData($private_key);
            if ($user) {
                if (isset($data['token'])) $token = $data['token']; else $token = null;
                if (isset($data['device'])) $device = $data['device']; else $device = null;
                if (isset($data['debug'])) $debug = $data['debug']; else $debug = null;
                $id = $user['id'];

                $trim = new Zend_Filter_StringTrim();
                $strip = new Zend_Filter_StripTags();
                $num = new Zend_Filter_Digits();
                $token = $trim->filter($token);
                $token = $strip->filter($token);
                $device = $trim->filter($device);
                $device = $strip->filter($device);
                $debug = $num->filter($debug);

                $db = new Application_Model_DbTable_Push();
                $answer = $db->addToken($id, $token, $device, $debug);

                if ($answer) {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '200'
                    ));
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '500'
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

    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     *
     * @SWG\Model(id="pushDelete")
     * @SWG\Property(name="device",type="string")
     * @SWG\Property(name="private_key",type="string")
     *
     * @SWG\Api(
     *   path="/push/",
     * @SWG\Operations(
     * @SWG\Operation(
     *       nickname="DeletePush",
     *       summary="Delete push.",
     *       httpMethod="DELETE",
     *       responseClass="void",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Have no permissions"
     *          )
     *       ),
     *
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="string"
     *         ),
     * @SWG\Parameter(
     *           name="device",
     *           description="device",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="string"
     *         )
     *     )
     *   )
     * )
     */
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $private_key = $this->_request->getParam('private_key');
        $device = $this->_request->getParam('device');
        if ($private_key && $private_key != null && $private_key != '' && $device != null) {
            $user = Application_Model_DbTable_Users::getUserData($private_key);
            if ($user) {
                $id = $user['id'];

                $trim = new Zend_Filter_StringTrim();
                $strip = new Zend_Filter_StripTags();
                $device = $trim->filter($device);
                $device = $strip->filter($device);

                $db = new Application_Model_DbTable_Push();
                $answer = $db->deletePush($id,$device);

                if ($answer) {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '200'
                    ));
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '500'
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