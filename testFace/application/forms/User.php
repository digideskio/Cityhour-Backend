<?php

class Application_Form_User extends Zend_Form
{

    protected $data;

    public function init()
    {
        $this->setName('Form');
        $this->setMethod('post');
        $this->addAttribs(array(
            'class' => 'form-inline'
        ));

        $validator_len_255 = new Zend_Validate_StringLength(0, 255);
        $validator_len_255->setMessage('Больше разрешенной максимальной длины в 255 символов');

        $validator_text = new Zend_Validate_Alnum();
        $db_validate = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'test_users',
            'field' => 'name',
            'exclude' => array(
                'field' => 'id',
                'value' => $this->data
            )
        ));
        $db_validate->setMessage('Такое имя уже существует');
        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->addValidator($validator_text)
            ->addValidator($db_validate)
            ->addValidator($validator_len_255)
            ->setAttribs(array(
                'class' => 'form-control input-lg',
                'placeholder' => 'Название'
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

        $is_free = new Zend_Form_Element_Radio('is_free');
        $is_free->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setMultiOptions(array(
                0 => 'Off',
                1 => 'On',
            ))
            ->setAttribs(array(
                'class' => 'input',
                'placeholder' => 'On'
            ))
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $validator_int = new Zend_Validate_Int();
        $offset = new Zend_Form_Element_Text('offset');
        $offset->setRequired(false)
            ->addFilter("StripTags")
            ->addFilter('StringTrim')
            ->setValue(7200)
            ->addValidator($validator_int)
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
        $submit->setLabel('Добавить пользователя')
            ->setDecorators(array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span'))
            ));

        $this->addElements(array($name, $industry, $goal,$is_free,$offset, $city, $submit));
    }

    public function setData($data) {
        $this->data = $data;
    }


}

