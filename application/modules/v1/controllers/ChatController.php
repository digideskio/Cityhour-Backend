<?php


/**
 * @SWG\Resource(
 *  resourcePath="/chat"
 * )
 */

class V1_ChatController extends Zend_Rest_Controller
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
     *   path="/chat/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="GET",
     *       summary="Get user messages.",
     *       type="void",
     *       nickname="GetUserMessages",
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
     *         ),
     * @SWG\Parameter(
     *           name="from",
     *           description="from",
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
        $from = $this->_request->getParam('from');
        if ($token && is_numeric($from)) {
            $user = Application_Model_DbTable_Users::authorize($token);

            $this->_helper->json->sendJson(array(
                'body' => (new Application_Model_DbTable_Chat())->getMessages($user,$from),
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
     * @SWG\Model(id="ChatTalks")
     * @SWG\Property(name="private_key",type="string")
     *
     * @SWG\Api(
     *   path="/chat/",
     * @SWG\Operations(
     * @SWG\Operation(
     *       nickname="GetChatTalks",
     *       summary="Get Chat Talks.",
     *       method="POST",
     *       type="void",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Have no permissions"
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
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=false,
     *           allowMultiple="false",
     *           type="ChatTalks"
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

            $this->_helper->json->sendJson(array(
                'body' => (new Application_Model_DbTable_Chat())->getTalks($user),
                'errorCode' => '200'
            ));
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
     * @SWG\Api(
     *   path="/chat/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="DELETE",
     *       summary="Delete chat.",
     *       type="void",
     *       nickname="DeleteChat",
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
        $token = $this->_request->getParam('private_key');
        $id = $this->_request->getParam('id');
        if ($token && is_numeric($id)) {
            $user = Application_Model_DbTable_Users::authorize($token);
            $this->_helper->json->sendJson(array(
                'errorCode' => (new Application_Model_DbTable_Chat())->deleteChat($user,$id)
            ));
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