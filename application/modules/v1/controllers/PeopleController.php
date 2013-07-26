<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/people"
 * )
 */

class V1_PeopleController extends Zend_Rest_Controller
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
     *   path="/people/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="GET",
     *       summary="Get People.",
     *       responseClass="void",
     *       nickname="GetPeople",
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
     * @SWG\Parameter(
     *           name="data_from",
     *           description="Data from",
     *           paramType="query",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="timestamp"
     *         ),
     * @SWG\Parameter(
     *           name="data_to",
     *           description="Data to",
     *           paramType="query",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="timestamp"
     *         ),
     * @SWG\Parameter(
     *           name="city",
     *           description="City",
     *           paramType="query",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="int"
     *         ),
     * @SWG\Parameter(
     *           name="industry",
     *           description="Industry",
     *           paramType="query",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="int"
     *         ),
     * @SWG\Parameter(
     *           name="goals",
     *           description="Goals",
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
        $token = $this->_request->getParam('private_key');
        $data_from = $this->_request->getParam('data_from');
        $data_to = $this->_request->getParam('data_to');
        $city = $this->_request->getParam('city');
        if ($token && $token != null && $token != '' && is_numeric($data_from) && is_numeric($data_to)) {
            $db = new Application_Model_DbTable_Users();
            $user = Application_Model_DbTable_Users::getUserData($token);
            if ($user) {
                $industry = $this->_request->getParam('industry');
                $goals = $this->_request->getParam('goals');

                $res = $db->getPeople($user,$data_from,$data_to,$city,$industry,$goals);
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