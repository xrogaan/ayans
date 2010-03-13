<?php

if (APPLICATION_ENVIRONMENT != 'production') {
	error_reporting(E_ALL);
}

function exception_handler($exception) {
  echo "Exception non attrapée : " , $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler');

require INCLUDES_PATH.'markdown.php';
require INCLUDES_PATH.'templates.php';
require INCLUDES_PATH.'filters.php';
require INCLUDES_PATH.'news-node.php';
