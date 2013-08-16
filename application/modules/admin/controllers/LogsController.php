<?php

class Admin_LogsController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('login', 'auth');
        }
    }

    public function indexAction()
    {
        $db = new Application_Model_DbTable_Logger();
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
        $this->view->search = $search;
    }

    public function deleteAction()
    {
        $db = new Application_Model_DbTable_Logger();
        $db->clearData();
        $this->_helper->redirector('index', 'logs');
    }

}

