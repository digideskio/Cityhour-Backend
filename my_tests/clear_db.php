<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();

$all = false;

$db->clearTable('calendar');
$db->clearTable('chat');
$db->clearTable('complaints');
$db->clearTable('email_users');
$db->clearTable('logger');
$db->clearTable('map');
$db->clearTable('notifications');
$db->clearTable('push_messages');
$db->clearTable('user_contacts_wait');
$db->clearTable('user_friends');



if ($all) {
    $db->clearTable('city');
    $db->clearTable('place');
    $db->clearTable('push');
    $db->clearTable('user_jobs');
    $db->clearTable('user_languages');
    $db->clearTable('user_photos');
    $db->clearTable('user_settings');
    $db->clearTable('user_skills');
    $db->clearTable('users');
}
