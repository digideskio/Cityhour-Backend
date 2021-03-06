<?php


/**
 * @SWG\Resource(
 *  resourcePath="/auth"
 * )
 */

class V1_AuthController extends Zend_Rest_Controller
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
     * @SWG\Model(id="registerParams")
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="lastname",type="string"),
     * @SWG\Property(name="email",type="string"),
     * @SWG\Property(name="industry_id",type="integer"),
     * @SWG\Property(name="summary",type="string"),
     * @SWG\Property(name="photo_id",type="integer"),
     * @SWG\Property(name="phone",type="string"),
     * @SWG\Property(name="business_email",type="string"),
     * @SWG\Property(name="skype",type="string"),
     * @SWG\Property(name="city",type="string"),
     * @SWG\Property(name="country",type="string"),
     * @SWG\Property(name="offset",type="integer"),
     * @SWG\Property(name="facebook_key",type="string"),
     * @SWG\Property(name="facebook_id",type="string"),
     * @SWG\Property(name="linkedin_id",type="string"),
     * @SWG\Property(name="linkedin_key",type="string"),
     * @SWG\Property(name="skills",type="Array()"),
     * @SWG\Property(name="languages",type="Array()"),
     * @SWG\Property(name="jobs",type="array",items="$ref:jobsParams"),
     * @SWG\Property(name="education",type="array",items="$ref:educationParams")
     *
     *
     * @SWG\Model(id="jobsParams")
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="company",type="string"),
     * @SWG\Property(name="current",type="string"),
     * @SWG\Property(name="start_time",type="string"),
     * @SWG\Property(name="end_time",type="string")
     *
     *
     * @SWG\Model(id="educationParams")
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="company",type="string"),
     * @SWG\Property(name="start_time",type="string"),
     * @SWG\Property(name="end_time",type="string")
     *
     *
     * @SWG\Api(
     *   path="/auth/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="Register.",
     *       type="void",
     *       nickname="Register",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="402",
     *            message="Not all params correct."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="300",
     *            message="User exist."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="registerParams"
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
        if (isset($data['city'])) $city = $data['city']; else $city = false;
        $valid_email = new Zend_Validate_EmailAddress();

        if ($valid_email->isValid($email) && $city && isset($data['jobs'][0])) {
            $db = new Application_Model_DbTable_Users();
            if ($db->getUser($email,false,'email',false)) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '300'
                ));
            }
            else {
                $id = $db->registerUser($data);
                if (is_numeric($id)) {
                    $this->_helper->json->sendJson(array(
                        'body' => $db->getUser($id,false,'id',true,true),
                        'errorCode' => '200'
                    ));
                }
                else {
                    $this->_helper->json->sendJson(array(
                        'body' => $id,
                        'errorCode' => '500'
                    ));
                }
            }
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => '402'
            ));
        }
    }

    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        /**
         *
         * @SWG\Model(id="loginParams")
         * @SWG\Property(name="type",type="integer"),
         * @SWG\Property(name="token",type="string")
         *
         *
         * @SWG\Api(
         *   path="/auth/login/",
         *   @SWG\Operations(
         *     @SWG\Operation(
         *       method="PUT",
         *       summary="Login.",
         *       type="void",
         *       nickname="login",
         *       notes="",
         *       @SWG\ResponseMessages(
         *          @SWG\ResponseMessage(
         *            code="400",
         *            message="Invalid login type."
         *          ),
         *          @SWG\ResponseMessage(
         *            code="409",
         *            message="Token not correct."
         *          ),
         *          @SWG\ResponseMessage(
         *            code="404",
         *            message="User not registered."
         *          ),
         *          @SWG\ResponseMessage(
         *            code="405",
         *            message="User not found socNetwork."
         *          )
         *       ),
         * @SWG\Parameter(
         *           name="json",
         *           description="json",
         *           paramType="body",
         *           required=true,
         *           allowMultiple="false",
         *           type="loginParams"
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