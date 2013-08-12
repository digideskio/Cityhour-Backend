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
     * @SWG\Model(id="inviteFriendsFind")
     * @SWG\Property(name="token",type="string")
     * @SWG\Property(name="type",type="int")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/inviteFriends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
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
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="inviteFriendsFind"
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

        if (isset($data['token'])) $token = $data['token']; else $token = false;
        if (isset($data['private_key'])) $private_key = $data['private_key']; else $private_key = false;
        if (isset($data['type'])) $type = $data['type']; else $type = false;

        if ($private_key && $private_key != null && $private_key != '') {
            $db_types = new Application_Model_Types();
            $types = $db_types->getInvetes();
            if (array_key_exists($type, $types)) {
                $db = new Application_Model_DbTable_UserContactsWait();
                $user = Application_Model_DbTable_Users::getUserData($private_key);
                if ($user) {
                    $res = $db->getAll($user,$type,$token);
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
        elseif ($token && $type == 4) {
            $db = new Application_Model_DbTable_UserContactsWait();
            $res = $db->getAll(false,$type,$token);
            $this->_helper->json->sendJson(array(
                'body' => $res,
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