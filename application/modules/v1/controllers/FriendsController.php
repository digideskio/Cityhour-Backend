<?php


/**
 * @SWG\Resource(
 *  resourcePath="/friends"
 * )
 */

class V1_FriendsController extends Zend_Rest_Controller
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
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="GET",
     *       summary="Get Friends.",
     *       type="void",
     *       nickname="GetFriends",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
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
     *         )
     *     )
     *   )
     * )
     */
    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $token = $this->_request->getParam('private_key');
        if ($user = Application_Model_DbTable_Users::authorize($token)) {
            $this->_helper->json->sendJson(array(
                'body' => (new Application_Model_DbTable_Friends())->getAll($user),
                'errorCode' => '200'
            ));
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
     * @SWG\Property(name="id",type="integer")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="Add friend.",
     *       type="void",
     *       nickname="InviteFriend",
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
     *            code="404",
     *            message="Id not found."
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
     *           type="addFriendParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['id']) && is_numeric($data['id'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);
            $db = new Application_Model_DbTable_Friends();
            $res = $db->addFriend($data['id'],$user);
            if ($res === 301) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '301'
                ));
            }
            elseif ($res) {
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
                'errorCode' => '400'
            ));
        }
    }


    /**
     *
     * @SWG\Model(id="AnswerFriendParams")
     * @SWG\Property(name="id",type="integer")
     * @SWG\Property(name="status",type="integer")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="PUT",
     *       summary="Answer friend request.",
     *       type="void",
     *       nickname="AnswerFriend",
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
     *            code="404",
     *            message="Id not found."
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
     *           type="AnswerFriendParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['id']) && is_numeric($data['id']) && isset($data['status']) && is_numeric($data['status'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);
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
     *       method="DELETE",
     *       summary="Delete Friend.",
     *       type="void",
     *       nickname="DeleteFriend",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="400",
     *            message="Not all params given."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="500",
     *            message="Server problem."
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
     *           type="integer"
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

        if ($token && is_numeric($id)) {
            $user = Application_Model_DbTable_Users::authorize($token);

            $res = (new Application_Model_DbTable_Friends())->deleteFriends($user,$id);
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