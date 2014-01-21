<?php
use SiteMaster\Core\Config;

/**********************************************************************************************************************
 * php related settings
 */

ini_set('display_errors', true);

error_reporting(E_ALL);

Config::set('URL', 'http://sitemaster.dev/'); //Trailing slash is important

/**********************************************************************************************************************
 * DB related settings
 */
Config::set('DB_HOST'     , 'localhost');
Config::set('DB_USER'     , 'sitemaster');
Config::set('DB_PASSWORD' , 'password');
Config::set('DB_NAME'     , 'sitemaster');

/**********************************************************************************************************************
 * Other settings, including theme
 */
Config::set('THEME', 'foundation');


/**********************************************************************************************************************
 * Plugin related settings
 */
Config::set('PLUGINS', array(
    'example' => array('setting'=>'value'),
    'theme_foundation' => array('setting'=>'value'),
    'auth_google' => array('setting'=>'value'),
));

/**********************************************************************************************************************
 * unit test settings
 */
Config::set('TEST_DB_HOST'     , 'localhost');
Config::set('TEST_DB_USER'     , 'user');
Config::set('TEST_DB_PASSWORD' , 'password');
Config::set('TEST_DB_NAME'     , 'database');