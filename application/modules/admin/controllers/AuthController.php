<?php

class Admin_AuthController extends Zend_Controller_Action
{

    public function init()
    {

    }

    public function indexAction() {
        $this->_helper->redirector('login', 'auth');
    }

    public function doLogin($login, $password) {
        if (($login_result = Application_Model_DbTable_Admins::login($login, $password)) === true) {
            $this->_helper->redirector('index', 'index');
        } elseif ($login_result == -1)
            $this->view->err = 'Wrong login or password';
        elseif ($login_result == -2) {
            Zend_Auth::getInstance()->clearIdentity();
            $this->view->err = 'Admin Blocked';
        }
    }

    public function loginAction() {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index');
        }
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->layout->disableLayout();

            $form = new Admin_Form_Login();
            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                    $login = $formData["login"];
                    $password = $formData["password"];
                    $this->doLogin($login, $password);
                } else
                    $this->view->err = 'Неправильные данные';
            }
            $this->view->form = $form;
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }


}

