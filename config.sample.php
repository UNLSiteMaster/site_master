<?php
use SiteMaster\Config;

/**********************************************************************************************************************
 * php related settings
 */

ini_set('display_errors', true);

error_reporting(E_ALL);

Config::set('URL', 'http://localhost/sitemaster/'); //Trailing slash is important

/**********************************************************************************************************************
 * DB related settings
 */
Config::set('DB_HOST'     , 'localhost');
Config::set('DB_USER'     , 'user');
Config::set('DB_PASSWORD' , 'password');
Config::set('DB_NAME'     , 'database');

/**
\SiteMaster\Config::set('PLUGINS', array(
    'W3C_HTML_VALIDATOR' => array(),
    'W3C_CSS_VALIDATOR' => array(),
    'LINK_CHECKER' => array(),
));
*/