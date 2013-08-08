<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/friends"
 * )
 */

class V1_FriendsController extends Zend_Rest_Controller
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
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Get Friends.",
     *       responseClass="void",
     *       nickname="GetFriends",
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
                $db = new Application_Model_DbTable_Friends();
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
     * @SWG\Model(id="addFriendParams")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Add friend.",
     *       responseClass="void",
     *       nickname="InviteFriend",
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
     *          @SWG\ErrorResponse(
     *            code="404",
     *            reason="Id not found."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="addFriendParams"
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
        if ($token && $token != null && $token != '' && isset($data['id']) && is_numeric($data['id'])) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Friends();
                if ($db->addFriend($data['id'],$user)) {
                    $this->_helper->json->sendJson(array(
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


    /**
     *
     * @SWG\Model(id="AnswerFriendParams")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="status",type="int")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
     *       summary="Answer friend request.",
     *       responseClass="void",
     *       nickname="AnswerFriend",
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
     *          @SWG\ErrorResponse(
     *            code="404",
     *            reason="Id not found."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="AnswerFriendParams"
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
        if ($token && $token != null && $token != '' && isset($data['id']) && is_numeric($data['id']) && isset($data['status']) && is_numeric($data['status'])) {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_DbTable_Friends();
                if ($db->answer($data['id'],$data['status'],$user)) {
                    $this->_helper->json->sendJson(array(
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

    /**
     *
     * @SWG\Api(
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="DELETE",
     *       summary="Delete Friend.",
     *       responseClass="void",
     *       nickname="DeleteFriend",
     *       notes="",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="401",
     *            reason="Authentication failed."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params given."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="500",
     *            reason="Server problem."
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
                $db = new Application_Model_DbTable_Friends();
                $res = $db->deleteFriends($user,$id);
                if ($res === true) {
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