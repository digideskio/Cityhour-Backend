<?php

class Application_Model_Texts
{

    public static function push() {
        return array(
            0 => 'New meeting request', // 'Meeting request',
            1 => 'Meeting canceled', // 'Meeting canceled',
            2 => 'Meeting Request Accepted', // 'Meeting request accepted',
            3 => 'New message', // Not Used
            4 => 'Friend request', // 'Friend request',
            5 => 'New message', // 'Chat message',
            6 => 'New meeting request', // 'Meeting request user not free',
            7 => 'System', // Custom
            8 => 'Up-coming meeting' // 'Meeting come'
        );
    }


    public static function notification($data = false) {
        return array(
            0 => 'Friend request', // 'Friend request',
            1 => '$$$name$$$ accepted your contact request', // 'Friends accept',
            2 => 'Friends reject', // Not Used
            3 => 'New meeting request', // 'Meeting request',
            4 => '$$$name$$$ accepted your meeting invite on $$$date$$$ at '.$data['place'], // 'Meeting accept',
            5 => 'Meeting reject', // Not Used
            6 => '$$$name$$$ canceled meeting on $$$date$$$ at '.$data['place'], // 'Meeting canceled',
            7 => 'Your LinkedIn contact $$$name$$$ joined CityHour', // 'User registered linkedin',
            8 => 'Your Facebook friend $$$name$$$ joined CityHour', // 'User registered facebook',
            9 => 'New meeting request', // 'Meeting request user not free',
            10 => 'You have meeting with $$$name$$$ at '.$data['place'].' soon.', // 'Meeting come',
            11 => 'Don’t forget to rate meeting with $$$name$$$ on $$$date$$$ at '.$data['place'], // 'Rate meeting',
            12 => 'System' // Custom
        );
    }


}

