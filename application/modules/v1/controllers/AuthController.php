<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/auth"
 * )
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


    /**
     *
     * @SWG\Model(id="registerParams")
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="lastname",type="string"),
     * @SWG\Property(name="email",type="string"),
     * @SWG\Property(name="skype",type="string"),
     * @SWG\Property(name="facebook_key",type="string"),
     * @SWG\Property(name="linkedin_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/auth/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Register.",
     *       responseClass="void",
     *       nickname="Register",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params correct."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="registerParams"
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
        if (isset($data['email'])) $email = $data['email']; else $email = false;
        $valid_email = new Zend_Validate_EmailAddress();

        if ($valid_email->isValid($email)) {
            $db = new Application_Model_DbTable_Users();
            if ($db->emailCheck($email)) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '300'
                ));
            }
            else {
                $filter = new Zend_Filter_StripTags();
                $userData = array(
                    'email' => $email,
                    'private_key' => uniqid(sha1(time()), false)
                );

                if (isset($data['skype'])) $userData['skype'] = $filter->filter($data['skype']);
                if (isset($data['name'])) $userData['name'] = $filter->filter($data['name']);
                if (isset($data['lastname'])) $userData['lastname'] = $filter->filter($data['lastname']);
                if (isset($data['facebook_key'])) $userData['facebook_key'] =  $filter->filter($data['facebook_key']);
                if (isset($data['photo_id'])) $userData['photo'] =  $filter->filter($data['photo_id']);
                if (isset($data['facebook_id'])) $userData['facebook_id'] =  $filter->filter($data['facebook_id']);
                if (isset($data['linkedin_key'])) $userData['linkedin_key'] = $filter->filter($data['linkedin_key']);
                if (isset($data['linkedin_id'])) $userData['linkedin_id'] = $filter->filter($data['linkedin_id']);


                try {
                    $id = $db->registerUser($userData);
                    $res = $db->getUserId($id);

                    $this->_helper->json->sendJson(array(
                        'body' => $res,
                        'errorCode' => '200'
                    ));
                }
                catch (Exception $e) {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '500',
                        'reason' => $e->getMessage()
                    ));
                }
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
        /**
         *
         * @SWG\Model(id="loginParams")
         * @SWG\Property(name="type",type="int"),
         * @SWG\Property(name="token",type="string")
         *
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
                        $res = $db->facebookLogin($token);
                        $this->_helper->json->sendJson($res);
                    }
                    else if ($type == 2) {
                        $res = $db->linkedinLogin($token);
                        $this->_helper->json->sendJson($res);
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