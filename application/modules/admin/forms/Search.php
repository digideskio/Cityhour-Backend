<?php

class Admin_Form_Search extends Zend_Form
{
    public function init() {
        $this->setName('search');
        $this->setMethod('post');
        $this->setAttrib('class','search');

        $search = new Zend_Form_Element_Text('search');
        $search->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setAttribs(array(
                'class' => 'form-control',
                'placeholder' => 'Search'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $submit = new Zend_Form_Element_Submit('ok');
        $submit->setAttrib('class','nodisplay');

        $this->addElements(array($search, $submit));
    }
}