<?php

//date_default_timezone_set('Europe/Kiev');
date_default_timezone_set("UTC");

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


// Connect composer
require_once '../vendor/autoload.php';

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library/'),
            get_include_path(),
        )));


ref::config('showUrls', false);

function l($text,$type) {
    $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/app.log');
    $logger = new Zend_Log($writer);
    $logger->$type($text);
}

//l(print_r($_POST,true),'info');
//l(print_r($_GET,true),'info');

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
    ->run();