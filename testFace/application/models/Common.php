<?php

class Application_Model_Common
{

    public static function renderErrors($form,$el) {
        $el = (string)$el;
        if (isset($form->getMessages()[$el])) {
            foreach ($form->getMessages()[$el] as $row) {
                echo '<span class="errors">'.$row.'</span>';
            }
        }
    }

    public static function hasErrors($form,$el) {
        $el = (string)$el;
        if (isset($form->getMessages()[$el])) {
            echo 'has-error';
        }
    }


}

