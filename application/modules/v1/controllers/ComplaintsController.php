<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/complaints"
 * )
 */

class V1_ComplaintsController extends Zend_Rest_Controller
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
     * @SWG\Model(id="addComplaintParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="type",type="string")
     * @SWG\Property(name="to",type="string")
     * @SWG\Property(name="dscr",type="string")
     *
     *
     * @SWG\Api(
     *   path="/complaints/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Add complaint.",
     *       responseClass="void",
     *       nickname="addComplaint",
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
     *           @SWG\ErrorResponse(
     *            code="403",
     *            reason="Already make complaint to this user."
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
     *           dataType="addComplaintParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['type']) && is_numeric($data['type']) && isset($data['to']) && is_numeric($data['to'])) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key']);

            $filter_alnum = new Zend_Filter_Alnum(true);
            $db = new Application_Model_DbTable_Complaints();
            $res = $db->addComplaint(array(
                'type' => $data['type'],
                'from' => $user['id'],
                'to' => $data['to'],
                'dscr' => $filter_alnum->filter($data['dscr'])
            ), $user['id'], $data['to']);
            if ($res === true) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '200'
                ));
            }
            elseif ($res === 403) {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '403'
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


    /**
     *
     * @SWG\Model(id="addFeedbackParams")
     * @SWG\Property(name="private_key",type="string")
     * @SWG\Property(name="dscr",type="string")
     *
     *
     * @SWG\Api(
     *   path="/complaints/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="PUT",
     *       summary="Add feedback.",
     *       responseClass="void",
     *       nickname="addFeedback",
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
     *           dataType="addFeedbackParams"
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
        if (isset($data['private_key']) && $data['private_key'] && isset($data['dscr']) && $data['dscr']) {
            $user = Application_Model_DbTable_Users::authorize($data['private_key'],false);

            $filter_alnum = new Zend_Filter_Alnum(true);
            $db = new Application_Model_DbTable_Complaints();
            if ($res = $db->addFeedback($user,$filter_alnum->filter($data['dscr']))) {
                $this->_helper->json->sendJson(array(
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

