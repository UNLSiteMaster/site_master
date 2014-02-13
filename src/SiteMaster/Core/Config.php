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
        'GRADE_SCALE'      => false
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

        //Special default case: CACHE_DIR
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

        //Special default case: CACHE_DIR
        if ($key == 'CACHE_DIR' && self::$data[$key] == false) {
            return  dirname(dirname(dirname(__FILE__))) . "/tmp/";
        }

        return self::$data[$key];
    }

    public static function set($key, $value)
    {
        return self::$data[$key] = $value;
    }
}