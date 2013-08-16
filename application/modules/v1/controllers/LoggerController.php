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
     * @SWG\Model(id="LogParams")
     * @SWG\Property(name="data_in",type="string")
     * @SWG\Property(name="data_out",type="string")
     * @SWG\Property(name="url",type="string")
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
     *           name="json",
     *           description="json",
     *           paramType="body",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="LogParams"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $data = @json_decode($this->getRequest()->getRawBody(),true);

        if ($data) {
            $db = new Application_Model_DbTable_Logger();
            $db->saveData($data);
        }
        else {
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/troubles.log');
            $logger = new Zend_Log($writer);
            $logger->info(print_r($this->getRequest()->getRawBody(),true));
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