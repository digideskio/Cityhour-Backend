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
            3 => 'address book',
            4 => 'register linkedin',
            5 => 'email'
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
            0 => 'Friend request',
            1 => 'Friends accept',
            2 => 'Friends reject'
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

    public static function push() {
        return array(
            0 => 'Meeting request',
            1 => 'Meeting canceled',
            2 => 'Meeting request accepted',
            3 => 'New message'
        );
    }

    public static function complaints() {
        return array(
            0 => 'User'
        );
    }

}

