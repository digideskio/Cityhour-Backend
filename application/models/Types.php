<?php

class Application_Model_Types
{

    public static function getLogin() {
        return array(
            1 => 'facebook',
            2 => 'linkedin'
        );
    }

    public static function getInvetes() {
        return array(
            1 => 'facebook',
            2 => 'linkedin',
            3 => 'address book',
            4 => 'register linkedin',
            5 => 'email',
            6 => 'register facebook'
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
            2 => 'Friends reject',
            3 => 'Meeting request',
            4 => 'Meeting accept',
            5 => 'Meeting reject',
            6 => 'Meeting canceled',
            7 => 'User registered linkedin',
            8 => 'User registered facebook',
            9 => 'Meeting request user not free',
            10 => 'Meeting come',
            11 => 'Rate meeting',
            12 => 'System',
            13 => 'Email meetings'
        );
    }

    public static function notificationTemplate() {
        return array(
            0 => 'Standart',
            1 => 'Red',
            2 => 'Requests',
            3 => 'System',
            4 => 'Rate meeting'
        );
    }

    public static function actions() {
        return array(
            0 => 'Meeting request',
            1 => 'Meeting details',
            2 => 'Friend request',
            3 => 'Contact profile',
            4 => 'Chat with user',
            5 => 'User profile',
            6 => 'Other user profile',
            7 => 'Meeting come',
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
            3 => 'New message',
            4 => 'Friend request',
            5 => 'Chat message',
            6 => 'Meeting request user not free',
            7 => 'System',
            8 => 'Meeting come',
            9 => 'User registered linkedin',
            10 => 'User registered facebook',
            11 => 'Friends accept',
        );
    }

    public static function complaints() {
        return array(
            0 => 'User'
        );
    }

    public static function personType() {
        return array(
            0 => 'No',
            1 => 'User_id',
            2 => 'Email'
        );
    }

}

