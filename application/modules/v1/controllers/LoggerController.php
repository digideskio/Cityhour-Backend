<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/logger"
 * )
 */

class V1_LoggerController extends Zend_Rest_Controller
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
     * @SWG\Api(
     *   path="/logger/",
     *   @SWG\Operations(
     *     @SWG\Operation(
     *       httpMethod="POST",
     *       summary="Log error.",
     *       responseClass="void",
     *       nickname="LogError",
     *       notes="",
     * @SWG\Parameter(
     *           name="data",
     *           description="log data",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="void"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/troubles.log');
        $logger = new Zend_Log($writer);
        $logger->info(Zend_Debug::dump($this->_request->getParam('data')));
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