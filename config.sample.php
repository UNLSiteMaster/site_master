<?php
use SiteMaster\Config;

/**********************************************************************************************************************
 * php related settings
 */

ini_set('display_errors', true);

error_reporting(E_ALL);

Config::set('URL', 'http://localhost/site_master/'); //Trailing slash is important

/**********************************************************************************************************************
 * DB related settings
 */
Config::set('DB_HOST'     , 'localhost');
Config::set('DB_USER'     , 'user');
Config::set('DB_PASSWORD' , 'password');
Config::set('DB_NAME'     , 'database');

Config::set('THEME', 'foundation');

\SiteMaster\Config::set('PLUGINS', array(
    'example' => array('setting'=>'value'),
    'theme_foundation' => array('setting'=>'value'),
));