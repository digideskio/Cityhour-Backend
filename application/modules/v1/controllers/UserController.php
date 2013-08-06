<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/user"
 * )
 */

class V1_UserController extends Zend_Rest_Controller
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
     *   path="/user/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Get user info.",
     *       responseClass="void",
     *       nickname="GetUser",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Authentication failed."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="404",
     *            reason="User not found)."
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
        $id = $this->_request->getParam('id');
        if ($token && $token != null && $token != '' && is_numeric($id)) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Users();
                $res = $db->getUser($id,$user);
                if ($res) {
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

    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     *
     * @SWG\Model(id="updateUserParams")
     * @SWG\Property(name="private_key",type="string"),
     * @SWG\Property(name="name",type="string"),
     * @SWG\Property(name="lastname",type="string"),
     * @SWG\Property(name="industry_id",type="int"),
     * @SWG\Property(name="summary",type="string"),
     * @SWG\Property(name="photo_id",type="int"),
     * @SWG\Property(name="phone",type="string"),
     * @SWG\Property(name="business_email",type="string"),
     * @SWG\Property(name="skype",type="string"),
     * @SWG\Property(name="city",type="string"),
     * @SWG\Property(name="skills",type="Array()"),
     * @SWG\Property(name="languages",type="Array()"),
     * @SWG\Property(name="jobs",type="Array",items="$ref:jobsParams"),
     * @SWG\Property(name="education",type="Array",items="$ref:educationParams")
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
     *       httpMethod="PUT",
     *       summary="Update User.",
     *       responseClass="void",
     *       nickname="updateUser",
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
     *           dataType="updateUserParams"
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
        if ($token && $token != null && $token != '') {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Users();
                $db->updateUser($user,$data);
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