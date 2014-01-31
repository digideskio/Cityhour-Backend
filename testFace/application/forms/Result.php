<?php

class Application_Form_Result extends Zend_Form
{

    public function init()
    {
        $this->setName('Form');
        $this->setMethod('post');
        $this->addAttribs(array(
            'class' => 'form-inline'
        ));

        $validator_len_255 = new Zend_Validate_StringLength(0, 255);
        $validator_len_255->setMessage('Больше разрешенной максимальной длины в 255 символов');

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


        $is_free = new Zend_Form_Element_Radio('is_free');
        $is_free->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions(array(
                0 => 'No',
                1 => 'Yes',
            ))
            ->setAttribs(array(
                'class' => 'input',
                'placeholder' => 'No'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));



        $submit = new Zend_Form_Element_Submit('ok');
        $submit->setAttrib('class','btn btn-primary btn-lg btn-category saveBoxButton');
        $submit->setLabel('Сохранить')
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $this->addElements(array($start_time,$end_time,  $is_free, $submit));
    }


}

