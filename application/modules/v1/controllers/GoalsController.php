<?php


/**
 * @SWG\Resource(
 *  resourcePath="/goals"
 * )
 */

class V1_GoalsController extends Zend_Rest_Controller
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
     *   path="/goals/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       method="GET",
     *       summary="Get goals.",
     *       type="void",
     *       nickname="GetGoals",
     *       notes=""
     *     )
     *   )
     * )
     */
    public function getAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $this->_helper->json->sendJson(array(
            'body' => (new Application_Model_DbTable_Goals())->getAll(),
            'errorCode' => '200'
        ));

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