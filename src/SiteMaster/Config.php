<?php
namespace SiteMaster;

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
        'THEME'            => 'bootstrap',
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