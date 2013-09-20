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
            2 => 'Not correct now'
        );
    }

    public static function Calendar() {
        return array(
            0 => 'Default',
            1 => 'Meeting Request',
            2 => 'Meeting Accepted',
            3 => 'Meeting Rejected',
            4 => 'Meeting Canceled',
            5 => 'Meeting Expired',
        );
    }

    public static function pushMessages() {
        return array(
            0 => 'Not sent',
            1 => 'Sent',
        );
    }

    public static function Users() {
        return array(
            0 => 'Active',
            1 => 'Block',
        );
    }

    public static function Complaints() {
        return array(
            0 => 'Not read',
            1 => 'Read',
        );
    }

}

