<?php
/**
 * @SWG\Resource(
 *  resourcePath="/user"
 * )
 */

class V1_UserController extends Zend_Rest_Controller
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
     *   path="/user/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="GET",
     *       summary="Get user info.",
     *       type="void",
     *       nickname="GetUser",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="404",
     *            message="User not found)."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="400",
     *            message="Not all params given."
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
        $token = $this->_request->getParam('private_key');
        $id = $this->_request->getParam('id');
        if ($token && is_numeric($id)) {
            $user = Application_Model_DbTable_Users::authorize($token,false);

            $db = new Application_Model_DbTable_Users();
            if ($res = $db->getUser($id,$user)) {
                $this->_helper->json->sendJson(array(
                    'body' => $res,
                    'errorCode' => '200'
                ));
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
     * @SWG\Model(id="UserKeysUpdate")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="facebook_key",type="string")
     * @SWG\Property(name="linkedin_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/user/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="User keys update.",
     *       type="void",
     *       nickname="keysUpdate",
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
     *            code="407",
     *            message="User blocked."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="409",
     *            message="Token not correct."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="416",
     *            message="User with this facebook exist."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="417",
     *            message="User with this linkedin exist."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="UserKeysUpdate"
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
            $user = Application_Model_DbTable_Users::authorize($data['private_key'],false);

            $db = new Application_Model_DbTable_Users();
            if ($res = $db->updateUserKeys($user,$data)) {
                $this->_helper->json->sendJson(array(
                        'body' => $res,
                        'errorCode' => '200'
                    ));
            }
            else {
                $this->_helper->json->sendJson(array(
                        'body' => $res,
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

    /**
     *
     * @SWG\Model(id="updateUserParams")
     * @SWG\Property(name="private_key",type="string"),
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="lastname",type="string"),
     * @SWG\Property(name="industry_id",type="integer"),
     * @SWG\Property(name="summary",type="string"),
     * @SWG\Property(name="phone",type="string"),
     * @SWG\Property(name="business_email",type="string"),
     * @SWG\Property(name="skype",type="string"),
     * @SWG\Property(name="city",type="string"),
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
     *
     *
     * @SWG\Api(
     *   path="/user/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="PUT",
     *       summary="Update User.",
     *       type="void",
     *       nickname="updateUser",
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
     *           type="updateUserParams"
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
        if (isset($data['private_key']) && $data['private_key']) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key'],false);

            $db = new Application_Model_DbTable_Users();
            if ($res = $db->updateUser($user,$data)) {
                $this->_helper->json->sendJson(array(
                    'body' => $res,
                    'errorCode' => '200'
                ));
            }
            else {
                $this->_helper->json->sendJson(array(
                    'body' => $res,
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