<?php

class CalendarController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $db = new Application_Model_DbTable_TestCalendar();
        $this->view->events = $db->getAll();
    }

    public function editAction()
    {
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
                    $db->saveEvent($formData,$id);
                    $form->populate($formData);
                    $this->view->save = true;
                }
                else {
                    $id = $db->addEvent($formData);
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
        // action body
    }


}





