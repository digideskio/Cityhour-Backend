<?php

class Admin_Form_User extends Zend_Form
{

    protected $data;

    public function init()
    {
        $this->setName('user_form');
        $this->setMethod('post');

        $validator_exist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'users',
            'field' => 'email',
            'exclude' => array(
                'field' => 'id',
                'value' => $this->data )
        ));
        $validator_r = new Zend_Validate_EmailAddress();
        $validator = new Zend_Validate_StringLength(array(
            'max' => 150,
            'encoding' => 'UTF-8'
        ));

        $this->addElement('hidden', 'html_1', array(
            'description' => '
                <div class="container_form">
            ',
            'ignore' => true,
            'decorators' => array(
                array('Description', array('escape'=>false, 'tag'=>'')),
            ),
        ));

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
            ->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setAttrib('size','150')
            ->setAttrib('class','form-control')
            ->addValidator($validator_exist)
            ->addValidator($validator_r)
            ->addValidator($validator)
            ->setDecorators(array(
                'ViewHelper', 'Errors',
                array('HtmlTag', array('tag' => 'div','class' => 'form-group')),
                array('Label', array('tag' => 'label'))

            ));
        $this->addElement($email);

        $db_result = Application_Model_Status::Users();
        $status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status:')
            ->setRequired(true)
            ->setAttrib('class','form-control')
            ->setMultiOptions($db_result)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setDecorators(array(
                'ViewHelper', 'Errors',
                array('HtmlTag', array('tag' => 'div','class' => 'form-group')),
                array('Label', array('tag' => 'label'))

            ));
        $this->addElement($status);


        $validator = new Zend_Validate_StringLength(array(
            'max' => 200,
            'encoding' => 'UTF-8'
        ));
        $reason_blocked = new Zend_Form_Element_Text('reason');
        $reason_blocked->setLabel('Reason to block:')
            ->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setAttrib('size','200')
            ->setAttrib('class','form-control')
            ->addValidator($validator)
            ->setDecorators(array(
                'ViewHelper', 'Errors',
                array('HtmlTag', array('tag' => 'div','class' => 'form-group')),
                array('Label', array('tag' => 'label'))

            ));
        $this->addElement($reason_blocked);


        $this->addElement('hidden', 'html_2', array(
            'description' => '<br><br>
                <a class="btn btn-danger pull-left" href="/admin/users/">Cancel</a>
            ',
            'ignore' => true,
            'decorators' => array(
                array('Description', array('escape'=>false, 'tag'=>'')),
            ),
        ));

        $submit = new Zend_Form_Element_Submit('ok');
        $submit->setAttrib('class','btn btn-success');
        $submit->setAttrib('style','margin-left: 10px;');
        $submit->setLabel('Done');
        $submit->setDecorators(array(
            'ViewHelper', 'Errors',
            array('HtmlTag', array('tag' => 'dd'))

        ));
        $this->addElement($submit);

        $this->addElement('hidden', 'html_3', array(
            'description' => '
                </div>
            ',
            'ignore' => true,
            'decorators' => array(
                array('Description', array('escape'=>false, 'tag'=>'')),
            ),
        ));
    }

    public function setData($data) {

        $this->data = $data;
    }
}