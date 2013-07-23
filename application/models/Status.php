<?php

class Application_Model_Status
{

    public static function Invite() {
        return array(
            0 => 'Not send',
            1 => 'Sent',
            2 => 'Friend',
            3 => 'Reject'
        );
    }

    public static function Friends() {
        return array(
            0 => 'Friend request',
            1 => 'Friend',
            2 => 'Friend deleted'
        );
    }

    public static function Notification() {
        return array(
            0 => 'Sent',
            1 => 'Read',
            2 => 'Accept',
            3 => 'Reject'
        );
    }

}

