<?php
namespace SiteMaster\Core;

use Monolog\Logger;

class Config
{
    CONST ENVIRONMENT_PRODUCTION = 'PRODUCTION';
    CONST ENVIRONMENT_TESTING    = 'TESTING';
    
    protected static $data = array(
        //SERVER RELATED SETTINGS
        'URL'              => false,        //base url for the application
        'CACHE_DIR'        => false,        //The Cache Dir

        //DB RELATED SETTINGS
        'DB_HOST'          => false,  //DATABASE HOST
        'DB_USER'          => false,  //DATABASE USER
        'DB_PASSWORD'      => false,  //DATABASE PASSWORD
        'DB_NAME'          => false,  //DATABASE NAME
        'PLUGINS'          => array(), //Plugin list and configuration
        
        //AUTO QUEUE SETTINGS
        'AUTO_QUEUE_RESCAN_PRODUCTION' => 31,  //Once every 31 days or 1 month
        'AUTO_QUEUE_RESCAN_DEVELOPMENT' => 62, //Once every 62 days or 2 months

        //OTHER SETTINGS
        'THEME'            => false,
        'GRADE_SCALE'      => false,
        'GRADE_POINTS'     => false,
        'MAX_HISTORY'      => 0,      //Max number of scans to keep per-site.  This is 2+max_history, because we need at LEAST 2
        'SCAN_PAGE_LIMIT'  => 2500,   //Max number of distinct pages to scan per site scan
        'RESTRICTED_URIS'  => array(), //Array of URIs that should not be scanned,
        'SITE_TITLE'       => 'SiteMaster', //The site title to display
        'USER_AGENT'       => 'sitemaster/1.0', //The user agent to send with HTTP requests
        'EMAIL_FROM'       => 'test@example.org',
        'EXAMPLE_TO_EMAIL' => 'test@example.org', //Used in testing
        'SITE_PASS_FAIL'   => false, //change to 'true' to grade everything as pass/fail, even sites.  GPA will become the percent of passing pages.
        'INCOMPLETE_LIMIT' => 25, //The max number of incomplete page scans before the daemon should restart and issue an alert (to help notify/prevent errors).
        'RESTART_INTERVAL' => 50, //The max number of pages to scan before routinely restarting the daemon (to fix potential errors with a long running daemon).
        'LOG_EMAIL_LEVEL'  => Logger::WARNING,
        'LOG_EMAIL_FROM'   => 'alert@sitemaster.com',
        'LOG_EMAIL_TO'     => '',
        'ALLOWED_DOMAINS'  => array(),
        'LINK_SCAN_LIMIT'  => 150, //the number of links to scan and log per scanned page (cached links and internal links are excluded from the limit)
        
        'EMAIL_WHITE_LIST'    => array(), //List of email addresses that can be sent to.  If empty, there are no restrictions
        'CHANGED_EMAIL_ROLES' => array('developer', 'admin'),  //Changed emails should be sent to these roles
        'EMAIL_SIGNATURE'     => 'The Web Developer Network ', //Custom email signature, if false, it will default to 'The $SITE_TITLE Team'
        'PAGE_LINK_TTL'       => '+1 hour',  //strtotime notation for the length of time that a link is to be considered 'fresh'.

        'SECONDS_BETWEEN_REQUESTS' => 1,
        
        //paths
        'PATH_NODE' => 'node',
        'HEADLESS_WIDTH' => 480,
        'HEADLESS_HEIGHT' => 800,
        'HEADLESS_WAIT' => 2500, //milliseconds before test execution (let the page run just a little bit first)
        'HEADLESS_TIMEOUT' => '5m', //timeout of the headless process, parameter should work with the `timeout` linux command.
        'XVFB_COMMAND' => false,
    
        //Loggers
        'PAGE_TITLE_LOGGER' => '\\SiteMaster\\Core\\Auditor\\Logger\\PageTitle',
    );

    private function __construct()
    {
        //Do nothing
    }

    public static function get($key)
    {
        if (!isset(self::$data[$key]) && $key == 'ENVIRONMENT') {
            return self::ENVIRONMENT_PRODUCTION;
        }
        
        if (!isset(self::$data[$key])) {
            return false;
        }

        //Special default case: GRADE_SCALE
        if ($key == 'GRADE_SCALE' && self::$data[$key] == false) {
            return array(
                '97' => Auditor\GradingHelper::GRADE_A_PLUS,
                '93' => Auditor\GradingHelper::GRADE_A,
                '90' => Auditor\GradingHelper::GRADE_A_MINUS,
                '87' => Auditor\GradingHelper::GRADE_B_PLUS,
                '83' => Auditor\GradingHelper::GRADE_B,
                '80' => Auditor\GradingHelper::GRADE_B_MINUS,
                '77' => Auditor\GradingHelper::GRADE_C_PLUS,
                '73' => Auditor\GradingHelper::GRADE_C,
                '70' => Auditor\GradingHelper::GRADE_C_MINUS,
                '67' => Auditor\GradingHelper::GRADE_D_PLUS,
                '63' => Auditor\GradingHelper::GRADE_D,
                '60' => Auditor\GradingHelper::GRADE_D_MINUS,
            );
        }

        //Special default case: GRADE_SCALE
        if ($key == 'GRADE_POINTS' && self::$data[$key] == false) {
            return array(
                Auditor\GradingHelper::GRADE_A_PLUS  => 4.0,
                Auditor\GradingHelper::GRADE_A       => 4.0,
                Auditor\GradingHelper::GRADE_A_MINUS => 3.67,
                Auditor\GradingHelper::GRADE_B_PLUS  => 3.33,
                Auditor\GradingHelper::GRADE_B       => 3.0,
                Auditor\GradingHelper::GRADE_B_MINUS => 2.67,
                Auditor\GradingHelper::GRADE_C_PLUS  => 2.33,
                Auditor\GradingHelper::GRADE_C       => 2.0,
                Auditor\GradingHelper::GRADE_C_MINUS => 1.67,
                Auditor\GradingHelper::GRADE_D_PLUS  => 1.33,
                Auditor\GradingHelper::GRADE_D       => 1.0,
                Auditor\GradingHelper::GRADE_D_MINUS => 0.67,
                Auditor\GradingHelper::GRADE_F       => 0,
            );
        }
        
        if ($key == 'EMAIL_SIGNATURE' && self::$data[$key] == false) {
            return 'The ' . self::$data['SITE_TITLE'] . ' Team';
        }

        //Special default case: CACHE_DIR
        if ($key == 'CACHE_DIR' && self::$data[$key] == false) {
            return  dirname(dirname(dirname(dirname(__FILE__)))) . "/tmp/";
        }

        //Alter the mysql host to be persistent
        if ($key == 'DB_HOST' && self::$data[$key] != false) {
            return 'p:' . self::$data[$key];
        }

        if ($key == 'XVFB_COMMAND' && self::$data[$key] == false) {
            return Util::getRootDir() . '/node_modules/xvfb-maybe/src/xvfb-maybe.js';
        }

        return self::$data[$key];
    }

    public static function set($key, $value)
    {
        return self::$data[$key] = $value;
    }
}