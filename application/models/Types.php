<?php

class Application_Model_Types
{

    public static function getLogin() {
        return array(
//            1 => 'facebook',
            2 => 'linkedin'
        );
    }

    public static function getInvetes() {
        return array(
            1 => 'facebook',
            2 => 'linkedin',
            3 => 'email',
        );
    }

    public static function invited() {
        return array(
            0 => false,
            1 => true
        );
    }

    public static function notification() {
        return array(
            0 => 'Friend request'
        );
    }

    public static function calendar() {
        return array(
            0 => 'Busy time',
            1 => 'Free time',
            2 => 'Meeting'
        );
    }

    public static function jobs() {
        return array(
            0 => 'Job',
            1 => 'Education'
        );
    }

}

