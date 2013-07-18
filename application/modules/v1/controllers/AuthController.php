<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/auth"
 * )
 *
 * @SWG\Model(id="loginParams")
 * @SWG\Property(name="type",type="int"),
 * @SWG\Property(name="token",type="string")
 */

class V1_AuthController extends Zend_Rest_Controller
{

    public function init()
    {
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

    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        /**
         *
         * @SWG\Api(
         *   path="/auth/login/",
         *   @SWG\Operations(
         *     @SWG\Operation(
         *       httpMethod="PUT",
         *       summary="Login.",
         *       responseClass="void",
         *       nickname="login",
         *       notes="",
         *       @SWG\ErrorResponses(
         *          @SWG\ErrorResponse(
         *            code="400",
         *            reason="Invalid login type."
         *          ),
         *          @SWG\ErrorResponse(
         *            code="409",
         *            reason="Token not correct."
         *          ),
         *          @SWG\ErrorResponse(
         *            code="404",
         *            reason="User not registered."
         *          ),
         *          @SWG\ErrorResponse(
         *            code="405",
         *            reason="User not found on Facebook."
         *          )
         *       ),
         * @SWG\Parameter(
         *           name="json",
         *           description="json",
         *           paramType="body",
         *           required="true",
         *           allowMultiple="false",
         *           dataType="loginParams"
         *         )
         *     )
         *   )
         * )
         */
        if ($this->_request->getParam('id') == 'login'){
            $body = $this->getRequest()->getRawBody();
            $data = Zend_Json::decode($body);
            if (isset($data['type'])) $type = $data['type']; else $type = false;
            if (isset($data['token'])) $token = $data['token']; else $token = false;

            if (array_key_exists($type, Application_Model_Types::getLogin())) {
                if($token) {
                    $db = new Application_Model_DbTable_Users();
                    if ($type == 1) {
                        if ($db->facebookCheck($token)) {
                            $this->_helper->json->sendJson($db->getUserFacebook($token,true,null));
                        }
                        else {
                            $config = $this->getInvokeArg('bootstrap')->getOption('facebook');
                            $this->_helper->json->sendJson($db->getUserFacebook($token,false,$config));
                        }
                    }
                    else if ($type == 2) {
                        if ($db->linkedinCheck($token)) {
                            $this->_helper->json->sendJson($db->getUserLinkedin($token,true,null));
                        }
                        else {
                            $this->_helper->json->sendJson($db->getUserLinkedin($token,false));
                        }
                    }
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '409'
                    ));
                }
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '400'
                ));
            }
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