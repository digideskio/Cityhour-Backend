<?php

class Admin_UsersController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('login', 'auth');
        }
    }

    public function indexAction()
    {
        $db = new Application_Model_DbTable_Users();
        $search = new Admin_Form_Search();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($data['search']) {
                $search->populate($data);
            }
            $data = array_merge($data, $search->getValues());
            $this->view->items = $db->getList($data);
        }
        else {
            $this->view->items = $db->getList();
        }
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $url = $config->userPhoto->url;
        $this->view->search = $search;
        $this->view->photoUrl = $url;
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');
        if (is_numeric($id)) {
            $db = new Application_Model_DbTable_Users();
            $form = new Admin_Form_User(array('data' => $id));
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $form->populate($data);
                if ($form->isValid($data)) {
                    $data = $form->getValues();
                    $db->saveUser($data,$id);
                }
            }
            else {
                $form->populate($db->getUser($id,false,'id',true,true));
            }
            $this->view->form = $form;
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }


}

