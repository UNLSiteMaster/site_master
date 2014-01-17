<?php
namespace SiteMaster\Core;

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
     * Connect using default settings
     */
    public static function connectTestDB()
    {
        self::setDB(Config::get('TEST_DB_HOST'), Config::get('TEST_DB_USER'), Config::get('TEST_DB_PASSWORD'), Config::get('TEST_DB_NAME'));
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
        $requestURI = substr($_SERVER['REQUEST_URI'], strlen(parse_url(\SiteMaster\Core\Config::get('URL'), PHP_URL_PATH)));

        return \SiteMaster\Core\Config::get('URL') . $requestURI;
    }

    public static function getRootDir()
    {
        return dirname(dirname(dirname(dirname(__FILE__))));
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
    
    public static function execMultiQuery($sql, $fail_ok = false)
    {
        $db = self::getDB();
        
        //Replace all instances of DEFAULTDATABASENAME with the config db name.
        $sql = str_replace('DEFAULTDATABASENAME', \SiteMaster\Core\Config::get("DB_NAME"), $sql);
        
        $return = array(
            'errors' => array(),
            'result' => false
        );

        try {
            $result = true;
            if ($db->multi_query($sql)) {
                do {
                    /* store first result set */
                    if ($result = $db->store_result()) {
                        $result->free();
                    }

                    if (!$db->more_results()) {
                        break;
                    }
                } while ($db->next_result());
            } else {
                $return['errors'] = "Query Failed: " . $db->error;
            }
        } catch (Exception $e) {
            $result = false;
            if (!$fail_ok) {
                return false;
            }
        }

        $return['result'] = $result;
        return $return;
    }
}