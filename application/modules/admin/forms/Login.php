<?php

class Admin_Form_Login extends Zend_Form
{

    public function init() {
        $this->setName('login');
        $this->setMethod('post');
        $this->setAttrib('class','form-signin');

        $login = new Zend_Form_Element_Text('login');
        $login->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setAttribs(array(
                'class' => 'form-control',
                'placeholder' => 'Login'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $password = new Zend_Form_Element_Password('password');
        $password->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setAttribs(array(
                'class' => 'form-control',
                'placeholder' => 'Password'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $submit = new Zend_Form_Element_Submit('ok');
        $submit->setAttrib('class','btn btn-info form-control');
        $submit->setLabel('Go to Adminland');

        $this->addElements(array($login, $password, $submit));
    }


}

