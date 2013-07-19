<?php

class Application_Model_Types
{

    public static function getLogin() {
        return array(
            1 => 'facebook',
            2 => 'linkedin'
        );
    }

    public static function invited() {
        return array(
            0 => false,
            1 => true
        );
    }

}

