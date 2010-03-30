<?php

if (APPLICATION_ENVIRONMENT != 'production') {
	error_reporting(E_ALL);
}

require_once('HTTP/Request2.php');
require_once(INCLUDES_PATH.'errornot.php');
$errornot = Services_ErrorNot::getInstance(true)
        ->setApi('cf12b1dfeb03cef327f92a1e')
        ->setUrl('http://127.0.0.1:3000')
        ->registerExceptionHandler();


function error_handler($errno, $errstr, $errfile, $errline, $errcontext ) {
    global $old;
    $er = Services_ErrorNot::getInstance();

    $send = $er->notify($errstr,
            NULL,
            array("[internal]$errfile:$errline ..."),
            array('params' => array('post' => $_POST, 'get' => $_GET, 'cookies' => $_COOKIE)),
            $_SERVER,
            $errcontext);

    if (!$send) {
        echo "Can't send to ".$er->getUrl();
    }
}
$old = set_error_handler("error_handler");

require INCLUDES_PATH.'markdown.php';
require INCLUDES_PATH.'templates.php';
require INCLUDES_PATH.'filters.php';
require INCLUDES_PATH.'news-node.php';
require INCLUDES_PATH.'Pages.php';
