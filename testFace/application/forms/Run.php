<?php

class Application_Form_Run extends Zend_Form
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
            ->setValue("10:00:00")
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => '10:00:00'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $end_time = new Zend_Form_Element_Text('end_time');
        $end_time->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setValue("20:00:00")
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => '20:00:00'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $db = new Application_Model_DbTable_Industries();
        $list = $db->getList();
        $industry = new Zend_Form_Element_Select('industry');
        $industry->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setValue(0)
            ->setMultiOptions($list)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Индустрия'
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
            ->setValue(0)
            ->setMultiOptions($list)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Цель'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $db = new Application_Model_DbTable_TestCity();
        $list = $db->getList();
        $city = new Zend_Form_Element_Select('city');
        $city->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions($list)
            ->setAttribs(array(
                'class' => 'form-control input',
                'placeholder' => 'Город'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $validator_int = new Zend_Validate_Int();
        $validator_max = new Zend_Validate_LessThan(25);
        $offset = new Zend_Form_Element_Text('offset');
        $offset->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setValue(2)
            ->addValidator($validator_int)
            ->addValidator($validator_max)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => '2'
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

        $this->addElements(array($start_time,$end_time,$industry,$goal,$city,$offset, $submit));
    }


}

