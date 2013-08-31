<?php

use Swagger\Annotations as SWG;
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

    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     *
     * @SWG\Model(id="syncUserSettingParams")
     * @SWG\Property(name="title",type="value")
     *
     *
     * @SWG\Api(
     *   path="/social/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Share to Social network.",
     *       responseClass="void",
     *       nickname="ShareToSocialNetwork",
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
     *           dataType="syncUserSettingParams"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $token = $this->_request->getParam('private_key');
        if ($token && $token != null && $token != '') {
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $db = new Application_Model_Linkedin();
                $this->_helper->json->sendJson(array(
                    'errorCode' => $db->makePost($user)
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