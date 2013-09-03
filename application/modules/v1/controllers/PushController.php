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
        if ($ids && $ids != ',') {
            $res = (new Application_Model_DbTable_PushMessages())->sendAll($ids);
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
     *          ),
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params given."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="407",
     *            reason="You blocked."
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
        if (isset($data['private_key']) && $data['private_key']) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);

            $validators = array(
                '*' => array('NotEmpty')
            );
            $filter_alnum = new Zend_Filter_Alnum(true);
            $filters = array(
                'token' => array('StringTrim','HtmlEntities',$filter_alnum),
                'device' => array('StringTrim','HtmlEntities',$filter_alnum),
                'debug' => array('StringTrim','HtmlEntities','Int')
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);

            if ($input->isValid()) {
                $db = new Application_Model_DbTable_Push();
                if ($db->addToken($user['id'], $input->getEscaped('token'), $input->getEscaped('device'), $input->getEscaped('debug'))) {
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
     *          ),
     *          @SWG\ErrorResponse(
     *            code="407",
     *            reason="You blocked."
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

        $validators = array(
            '*' => array('NotEmpty')
        );
        $filter_alnum = new Zend_Filter_Alnum(true);
        $filters = array(
            'device' => array('StringTrim','HtmlEntities',$filter_alnum)
        );
        $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());

        if ($input->isValid()) {
            $user = Application_Model_DbTable_Users::authorize($input->getUnescaped('private_key'));
            $db = new Application_Model_DbTable_Push();

            if ($db->deletePush($user['id'],$input->getEscaped('device'))) {
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