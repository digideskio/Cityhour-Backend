<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $db = new Application_Model_DbTable_TestUsers();
        $this->view->users = $db->getAll();
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');
        $id = (is_numeric($id)) ? $id:0;

        $db = new Application_Model_DbTable_TestUsers();
        $form = new Application_Form_Result(array('data' => (int)$id));

        $origData =  ($id) ? $db->getUser($id):array();

        if ($id) $form->getElement('ok')->setLabel("Сохранить");

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                if ($id) {
                    $db->saveUser($formData,$id);
                    $form->populate($formData);
                    $this->view->save = true;
                }
                else {
                    $id = $db->addUser($formData);
                    $this->_helper->redirector('edit', 'index',
                        null,
                        array('id' => $id
                        )
                    );
                }
            } else {
                $this->view->err = 'Не все поля заполнены корректно.';
            }
        }
        else {
            $form->populate($origData);
        }

        $this->view->name = (isset($origData['name'])) ? $origData['name']:false;
        $this->view->id = (isset($origData['id'])) ? $origData['id']:false;
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        if (is_numeric($id)) {
            (new Application_Model_DbTable_TestUsers())->deleteUser($id);
        }
        $this->_helper->redirector('index', 'index');
    }


}





