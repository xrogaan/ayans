<?php

error_reporting(E_ALL);

define('DEBUG',true);

// default sha1 : test
define('PASSWORD', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3');

define('DBH', 'sqlite:'.dirname(__FILE__).'/../news.sq3');

define('CACHE_PATH','cache/');
define('INCLUDES_PATH','includes/');