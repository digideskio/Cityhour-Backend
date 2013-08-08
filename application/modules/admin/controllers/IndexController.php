<?php

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('login', 'auth');
        }
    }

    public function indexAction()
    {
        // action body
    }


}

