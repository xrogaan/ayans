<?php

/**
 * For production mode set the value to 'production'
 */
define('APPLICATION_ENVIRONMENT', 'development');

// default sha1 : test
define('PASSWORD', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3');

/**
 * Config line to pass to PDO
 */
define('DBH', 'sqlite:'.dirname(__FILE__).'/../news.sq3');

define('CACHE_PATH',       dirname(__FILE__).'/../cache/');
define('PAGES_CACHE_PATH', CACHE_PATH . '/pages/');
define('INCLUDES_PATH',    dirname(__FILE__).'/../includes/');


if (isset($_ENV['TMP']) && !empty($_ENV['TMP']) && is_writable($_ENV['TMP'])) {
    define('TMP', $_ENV['TMP'].'/php/');
} else {
    define('TMP', CACHE_PATH . '/tmp/');
}

if (!file_exists(TMP)) {
    mkdir(TMP,0777,true);
}