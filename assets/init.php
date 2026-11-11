<?php
@ini_set('session.cookie_httponly',1);
@ini_set('session.use_only_cookies',1);
$https_session = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    $https_session = true;
}
if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'monkeycode-ai.live') !== false) {
    $https_session = true;
}
if ($https_session) {
    @ini_set('session.cookie_secure', 1);
}
@header("X-FRAME-OPTIONS: SAMEORIGIN");
if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
    exit("Required PHP_VERSION >= 5.5.0 , Your PHP_VERSION is : " . PHP_VERSION . "\n");
}
date_default_timezone_set('UTC');
session_start();
@ini_set('gd.jpeg_ignore_warning', 1);
require_once('includes/php_compat.php');
require_once('includes/cache.php');
require_once('includes/functions_general.php');
require_once('includes/tabels.php');
require_once('includes/functions_one.php');
require_once('includes/functions_two.php');
require_once('includes/functions_three.php');