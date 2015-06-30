<?php


/**
 * @SWG\Resource(
 *  resourcePath="/social"
 * )
 */

class V1_SocialController extends Zend_Rest_Controller
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
     *   path="/social/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="GET",
     *       summary="Get share.",
     *       type="void",
     *       nickname="GetSocial",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="404",
     *            message="Time Slot not found)."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="400",
     *            message="Not all params given."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="uid",
     *           description="Id of user",
     *           paramType="query",
     *           required=true,
     *           allowMultiple="false",
     *           type="string"
     *         ),
     * @SWG\Parameter(
     *           name="id",
     *           description="Slot id",
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
        $id = $this->_request->getParam('id');
        $uid = $this->_request->getParam('uid');
        if (is_numeric($id) && is_numeric($uid)) {
            $res = (new Application_Model_DbTable_Calendar())->getSocial($id,$uid);
            if (isset($res['user'])) {
                $this->_helper->json->sendJson(array(
                    'body' => $res,
                    'errorCode' => 200
                ));
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => $res
                ));
            }

        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => 400
            ));
        }
    }

    /**
     *
     * @SWG\Model(id="ShareCreateParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="id",type="integer")
     *
     *
     * @SWG\Api(
     *   path="/social/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="POST",
     *       summary="Share to Social network.",
     *       type="void",
     *       nickname="ShareToSocialNetwork",
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
     *            code="404",
     *            message="Not found slot to share."
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
     *           type="ShareCreateParams"
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

            $this->_helper->json->sendJson(array(
                'errorCode' => (new Application_Model_Linkedin())->makePost($user,$data['id'])
            ));
        }
        else {
            $this->_helper->json->sendJson(array(
                'errorCode' => 400
            ));
        }
    }


    /**
     *
     * @SWG\Model(id="UpdateFromLinkedin")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="type",type="integer")
     *
     *
     * @SWG\Api(
     *   path="/social/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="PUT",
     *       summary="Update user info from linkedin or Facebook.",
     *       type="void",
     *       nickname="UpdateUserFromLinkedin",
     *       notes="",
     *       @SWG\ResponseMessages(
     *          @SWG\ResponseMessage(
     *            code="401",
     *            message="Authentication failed."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="407",
     *            message="You blocked."
     *          ),
     *          @SWG\ResponseMessage(
     *            code="405",
     *            message="User not found socNetwork."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required=true,
     *           allowMultiple="false",
     *           type="UpdateFromLinkedin"
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
        $user = Application_Model_DbTable_Users::authorize($data['private_key']);
        $config = $this->getInvokeArg('bootstrap')->getOption('userPhoto');

        if (isset($data['type']) && $data['type'] === 1) {
            (new Application_Model_Facebook())->updateUser($user,$config);
        } else {
            (new Application_Model_Linkedin())->updateUser($user,$config);
        }

        $this->_helper->json->sendJson(array(
            'body' => (new Application_Model_DbTable_Users())->getUser($user['id'],$user),
            'errorCode' => 200
        ));
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