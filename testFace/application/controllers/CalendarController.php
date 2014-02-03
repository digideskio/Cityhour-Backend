<?php

class CalendarController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if ($id = $this->_request->getParam('id')) {
            $db = new Application_Model_DbTable_TestCalendar();
            $this->view->events = $db->getAll($id);
            $this->view->user = $id;
            $this->view->name = (new Application_Model_DbTable_TestUsers)->getUser($id)['name'];
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }

    public function editAction()
    {
        if ($uid = $this->_request->getParam('uid')) {
            $this->view->user = $uid;
            $id = $this->_request->getParam('id');
            $id = (is_numeric($id)) ? $id:0;

            $db = new Application_Model_DbTable_TestCalendar();
            $form = new Application_Form_Event(array('data' => (int)$id));

            $origData =  ($id) ? $db->getEvent($id):array();
            if ($id) $form->getElement('ok')->setLabel("Сохранить");

            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                    if ($id) {
                        $db->saveEvent($formData,$uid,$id);
                        $form->populate($formData);
                        $this->view->save = true;
                    }
                    else {
                        $id = $db->addEvent($formData,$uid);
                        $this->_helper->redirector('edit', 'calendar',
                            null,
                            array(
                                'id' => $id,
                                'uid' => $uid
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
        else {
            $this->_helper->redirector('index', 'index');
        }
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        $uid = $this->_request->getParam('uid');
        if (is_numeric($id) && is_numeric($uid)) {
            (new Application_Model_DbTable_TestCalendar())->deleteEvent($id);
        }
        $this->_helper->redirector('index', 'calendar',
            null,
            array(
                'id' => $uid
            )
        );
    }


}





