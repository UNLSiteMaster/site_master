<?php
namespace SiteMaster\Core;

class Config
{
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

        //OTHER SETTINGS
        'THEME'            => false,
        'GRADE_SCALE'      => false,
        'GRADE_POINTS'     => false,
        'MAX_HISTORY'      => 0,      //Max number of scans to keep per-site.  This is 2+max_history, because we need at LEAST 2
    
        //Loggers
        'PAGE_TITLE_LOGGER' => '\\SiteMaster\\Core\\Auditor\\Logger\\PageTitle',
    );

    private function __construct()
    {
        //Do nothing
    }

    public static function get($key)
    {
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

        //Special default case: CACHE_DIR
        if ($key == 'CACHE_DIR' && self::$data[$key] == false) {
            return  dirname(dirname(dirname(dirname(__FILE__)))) . "/tmp/";
        }

        return self::$data[$key];
    }

    public static function set($key, $value)
    {
        return self::$data[$key] = $value;
    }
}