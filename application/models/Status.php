<?php

class Application_Model_Status
{

    public static function Invite() {
        return array(
            0 => 'Not send',
            1 => 'Sent',
            2 => 'Friend'
        );
    }

    public static function Friends() {
        return array(
            0 => 'Friend request',
            1 => 'Friend',
            2 => 'Friend deleted'
        );
    }

}

