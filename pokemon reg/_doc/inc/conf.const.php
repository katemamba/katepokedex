<?php

$sHttp = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$sPhp_self = str_replace('\\', '', dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES))); // Remove backslashes for Windows compatibility

#################### CONSTANTS ####################


########## OTHER ##########

define('SELF', (substr($sPhp_self,-1) !== '/') ? $sPhp_self . '/' : $sPhp_self);
define('RELATIVE', SELF);
define('DEF_LANG', 'en');
define('TPL', 'base');


########## PATH ##########

define('ROOT_PATH', dirname(__DIR__) . '/');
define('DATA_PATH', ROOT_PATH . 'data/');

