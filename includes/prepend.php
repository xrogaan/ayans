<?php

if (APPLICATION_ENVIRONMENT != 'production') {
	error_reporting(E_ALL);
}

require_once('FirePHPCore/FirePHP.class.php');
$firephp = FirePHP::getInstance(true);
$firephp->registerErrorHandler(true);
$firephp->registerExceptionHandler();
$firephp->registerAssertionHandler(true, false);

require INCLUDES_PATH.'markdown.php';
require INCLUDES_PATH.'templates.php';
require INCLUDES_PATH.'filters.php';
require INCLUDES_PATH.'news-node.php';
