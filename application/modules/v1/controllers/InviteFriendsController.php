<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/InviteFriends"
 * )
 */



class V1_InviteFriendsController extends Zend_Rest_Controller
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
     *   path="/InviteFriends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Register.",
     *       responseClass="void",
     *       nickname="Register",
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
            $db = new Application_Model_DbTable_UserContactsWait();
            $id = Application_Model_DbTable_Users::getUserData($token)['id'];

            $res = $db->getAll($id);
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

    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
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