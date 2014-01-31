<?php

class ResultController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $id = $this->_request->getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->redirector('index', 'index');
        }

        $db = new Application_Model_DbTable_TestResult();
        $form = new Application_Form_Result(array('data' => (int)$id));

        $origData =  $db->getResult($id);

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $db->saveResult($formData,$id);
                $form->populate($formData);
                $this->view->save = true;
            } else {
                $this->view->err = 'Не все поля заполнены корректно.';
            }
        }
        else {
            $form->populate($origData);
        }

        $this->view->name = (new Application_Model_DbTable_TestUsers())->getUser($id)['name'];
        $this->view->form = $form;
    }


}

