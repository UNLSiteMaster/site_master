<?php
namespace SiteMaster;

use \DB\Connection;

class Util
{
    protected static $db = false;

    public static function setDB($host, $user, $password, $database)
    {
        self::$db = new \mysqli($host, $user, $password, $database);

        if (mysqli_connect_error()) {
            throw new RuntimeException('Database connection error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
        }

        self::$db->set_charset('utf8');

        //Set DB connection
        Connection::setDB(self::$db);
    }

    /**
     * Connect using default settings
     */
    public static function connectDB()
    {
        self::setDB(Config::get('DB_HOST'), Config::get('DB_USER'), Config::get('DB_PASSWORD'), Config::get('DB_NAME'));
    }

    /**
     * Connect to the database and return it
     * 
     * @return \mysqli
     */
    public static function getDB()
    {
        //If it isn't set yet, try to set it.
        if (!self::$db) {
            self::setDB(Config::get('DB_HOST'), Config::get('DB_USER'), Config::get('DB_PASSWORD'), Config::get('DB_NAME'));
        }

        return self::$db;
    }

    public static function epochToDateTime($time = false)
    {
        if (!$time) {
            $time = time();
        }

        return date("Y-m-d H:i:s", $time);
    }

    public static function makeClickableLinks($text) {
        return preg_replace('@((https?|file)://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#+%-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
    }

    public static function getCurrentURL()
    {
        $requestURI = substr($_SERVER['REQUEST_URI'], strlen(parse_url(\SiteMaster\Config::get('URL'), PHP_URL_PATH)));

        return \SiteMaster\Config::get('URL') . $requestURI;
    }

    public static function getRootDir()
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    /**
     * Parse the base path from the URL set in the config
     * 
     * @return string
     */
    public static function getBaseURLPath()
    {
        if (!$parts = parse_url(Config::get('URL'))) {
            return '/';
        }
        
        if (!isset($parts['path'])) {
            return '/';
        }
        
        return $parts['path'];
    }
}