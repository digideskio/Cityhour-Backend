<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/inviteFriends"
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
     *   path="/inviteFriends/",
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
     *         ),
     *  @SWG\Parameter(
     *           name="type",
     *           description="type",
     *           paramType="query",
     *           required="true",
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
        $token = $this->_request->getParam('private_key');
        $type = $this->_request->getParam('type');
        $db_types = new Application_Model_Types();
        $types = $db_types->getLogin();
        if ($token && $token != null && $token != '' && array_key_exists($type, $types)) {
            $db = new Application_Model_DbTable_UserContactsWait();
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $res = $db->getAll($user,$type);
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