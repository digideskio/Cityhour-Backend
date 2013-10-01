<?php

use Swagger\Annotations as SWG;
/**
 * @SWG\Resource(
 *  resourcePath="/photo"
 * )
 */

class V1_PhotoController extends Zend_Rest_Controller
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
     *   path="/photo/",
     * @SWG\Operations(
     * @SWG\Operation(
     *       nickname="UploadPhoto",
     *       summary="Upload photo.",
     *       httpMethod="POST",
     *       responseClass="void",
     *       @SWG\ErrorResponses(
     *          @SWG\ErrorResponse(
     *            code="400",
     *            reason="Not all params correct."
     *          ),
     *          @SWG\ErrorResponse(
     *            code="412",
     *            reason="Not image."
     *          )
     *       ),
     * @SWG\Parameter(
     *           name="file",
     *           description="file",
     *           paramType="query",
     *           required="true",
     *           allowMultiple="false",
     *           dataType="int"
     *         ),
     * @SWG\Parameter(
     *           name="private_key",
     *           description="private_key",
     *           paramType="query",
     *           required="false",
     *           allowMultiple="false",
     *           dataType="int"
     *         )
     *     )
     *   )
     * )
     */
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(200);

        $upload = new Zend_File_Transfer_Adapter_Http();
        $upload->addValidator('IsImage', false);

        $files = $upload->getFileInfo();
        if (isset($files['file'])) {
            $file = $files['file'];
            $rrr = uniqid(time(), false);
            if($upload->isValid('file')){
                $ext = pathinfo($file['name']);
                $filename = 'userPic_'.$rrr.'.'.$ext['extension'];

                $config = $this->getInvokeArg('bootstrap')->getOption('userPhoto');

                $private_key = $this->_request->getParam('private_key');
                $db = new Application_Model_DbTable_UserPhotos();
                $id = $db->makePhoto($rrr,$filename,$private_key,$config,$file['tmp_name']);

                $this->_helper->json->sendJson(array(
                    'body' => array(
                        'id' => $id['id'],
                        'url' => $config['url'].$id['url']
                    ),
                    'errorCode' => '200'
                ));
            }
            else {
                $this->_helper->json->sendJson(array(
                    'errorCode' => '412'
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