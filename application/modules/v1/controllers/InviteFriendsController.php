<?php


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
     * @SWG\Property(name="type",type="integer")
     * @SWG\Property(name="private_key",type="string")
     *
     *
     * @SWG\Api(
     *   path="/invite-friends/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="Get friends from social networks.",
     *       type="void",
     *       nickname="inviteFriends",
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
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="inviteFriendsFind"
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


        if (isset($data['type']) && is_numeric($data['type'])) $type = $data['type']; else $type = false;
        if ($type == 3 || $type == 5) {
            $token = $data['token'];
        }
        else {
            $validators = array(
                '*' => array('NotEmpty')
            );
            $filters = array(
                'token' => array('StringTrim','HtmlEntities')
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            $db_types = new Application_Model_Types();
            $types = $db_types->getInvetes();
            if (array_key_exists($type, $types)) {
                $token = $input->getEscaped('token');
                if (!$input->isValid()) {
                    $this->_helper->json->sendJson(array(
                        'errorCode' => '400'
                    ));
                }
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '400'
                ));
            }
        }


        if (isset($data['private_key']) && $data['private_key']) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);

            $db = new Application_Model_DbTable_UserContactsWait();
            $this->_helper->json->sendJson(array(
                'body' => $db->getAll($user,$type,$token),
                'errorCode' => '200'
            ));
        }
        elseif ($type == 4 || $type == 6) {
            $db = new Application_Model_DbTable_UserContactsWait();
            $this->_helper->json->sendJson(array(
                'body' => $db->getAll(false,$type,$token),
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