<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/notifications"
 * )
 */

class V1_NotificationsController extends Zend_Rest_Controller
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
     *   path="/notifications/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Get Notifications.",
     *       responseClass="void",
     *       nickname="GetNotifications",
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
     *            code="407",
     *            reason="You blocked."
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
     *           required="false",
     *           allowMultiple="false",
     *           dataType="int"
     *         )
     *     )
     *   )
     * )
     */
    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        if ($token = $this->_request->getParam('private_key')) {
            $user = Application_Model_DbTable_Users::authorize($token);
            $id = $this->_request->getParam('id');
            $this->_helper->json->sendJson(array(
                'body' => (new Application_Model_DbTable_Notifications())->getAll($user,$id),
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
     * @SWG\Model(id="counterNotificationParams")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/notifications/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Notification counters.",
     *       responseClass="void",
     *       nickname="CountNotification",
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
     *            code="407",
     *            reason="You blocked."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="counterNotificationParams"
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
                'body' => (new Application_Model_DbTable_Notifications())->getCounters($user),
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
     * @SWG\Model(id="readNotificationParams")
     * @SWG\Property(name="id",type="int")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/notifications/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
     *       summary="Read notification.",
     *       responseClass="void",
     *       nickname="ReadNotification",
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
     *            code="407",
     *            reason="You blocked."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="readNotificationParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['id']) && is_numeric($data['id'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);
            (new Application_Model_DbTable_Notifications())->read($data['id'],$user);
            $this->_helper->json->sendJson(array(
                'errorCode' => '200'
            ));
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