<?php

class Application_Form_Event extends Zend_Form
{

    public function init()
    {
        $validator_len_255 = new Zend_Validate_StringLength(0, 255);
        $validator_len_255->setMessage('Больше разрешенной максимальной длины в 255 символов');


        $db = new Application_Model_DbTable_TestUsers();
        $list = $db->getList();
        $user_id = new Zend_Form_Element_Select('user_id');
        $user_id->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions($list)
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Имя'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $user_id_second = new Zend_Form_Element_Select('user_id_second');
        $user_id_second->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions($list)
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Имя'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $start_time = new Zend_Form_Element_Text('start_time');
        $start_time->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => '09:00:00'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $end_time = new Zend_Form_Element_Text('end_time');
        $end_time->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => '09:00:00'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));


        $db = new Application_Model_DbTable_Goals();
        $list = $db->getList();
        $goal = new Zend_Form_Element_Select('goal');
        $goal->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions($list)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Цель'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $type = new Zend_Form_Element_Select('type');
        $type->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions(array(
                0 => 'Busy time',
                1 => 'Free time',
                2 => 'Meeting'
            ))
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Тип'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $status = new Zend_Form_Element_Select('status');
        $status->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions(array(
                0 => 'Default',
                1 => 'Meeting Request',
                2 => 'Meeting Accepted',
                3 => 'Meeting Rejected',
                4 => 'Meeting Canceled',
                5 => 'Meeting Expired'
            ))
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Тип'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));



        $submit = new Zend_Form_Element_Submit('ok');
        $submit->setAttrib('class','btn btn-primary btn-lg btn-category saveBoxButton');
        $submit->setLabel('Добавить event')
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $this->addElements(array($user_id,$user_id_second, $start_time, $end_time, $goal,$type,$status, $submit));
    }


}

