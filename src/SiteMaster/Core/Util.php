<?php
namespace SiteMaster\Core;

use DB\Connection;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

    /**
     * Validate a url and return a sanitized version of it
     * 
     * @param $url
     * @param bool $verify - set to true to verify a 200 level http response for the URL
     * @return string - the sanitized base_url
     * @throws HTTPConnectionException
     * @throws InvalidArgumentException
     */
    public static function validateBaseURL($url, $verify = false)
    {
        $valid_schemes = array('http', 'https');
        
        if (!$url_parts = parse_url($url)) {
            throw new InvalidArgumentException('Invalid URL', 400);
        }
        
        if (!isset($url_parts['host'])) {
            throw new InvalidArgumentException('Invalid host', 400);
        }
        
        if (!isset($url_parts['scheme'])) {
            $url_parts['scheme'] = 'http';
        }
        
        if (!in_array($url_parts['scheme'], $valid_schemes)) {
            throw new InvalidArgumentException('Invalid scheme', 400);
        }

        if (!isset($url_parts['path'])) {
            throw new InvalidArgumentException('A path must be set', 400);
        }
        
        if (isset($url_parts['query'])) {
            throw new InvalidArgumentException('A query string must not be set', 400);
        }

        if (isset($url_parts['fragment'])) {
            throw new InvalidArgumentException('A fragment must not be set', 400);
        }

        if (isset($url_parts['user'])) {
            throw new InvalidArgumentException('A user must not be set', 400);
        }

        if (isset($url_parts['pass'])) {
            throw new InvalidArgumentException('A password must not be set', 400);
        }
        
        if (substr($url_parts['path'], -1) != '/') {
            throw new PathRequiredException('The Path must end in a /', 400);
        }
        
        //sanitize because things like http://www.test.com/?# are valid with the above
        $port = '';
        if (isset($url_parts['port'])) {
            $port = ':' . (int)$url_parts['port'];
        }
        $base_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $port . $url_parts['path'];
        
        if ($verify) {
            $http_info = self::getHTTPInfo($base_url);
            if (!$http_info['okay']) {
                throw new HTTPConnectionException('Unable to connect to ' . $base_url . ' HTTP Code: ' . $http_info['http_code'], 400);
            }
        }
        
        return $base_url;
    }

    /**
     * @param $url
     * @param array $options array of CURL options used in curl_setop_array
     *
     * @return array
     */
    public static function getHTTPInfo($url, $options = array())
    {
        $curl = curl_init($url);
        
        $default_options = array(
            CURLOPT_NOBODY         => true, //Attempt a HEAD request by default to save bandwidth
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT      => 'UNL_SITEMASTER/1.0',
            CURLOPT_FILE           => fopen('/dev/null', 'w+')
        );
        
        $options = $options + $default_options;

        curl_setopt_array($curl, $options);
        
        curl_exec($curl);

        $http_status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $effective_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $curl_error_no = curl_errno($curl);

        curl_close($curl);
        
        //Always sleep after requesting data, this will help prevent loops from flooding a server.
        sleep(Config::get('SECONDS_BETWEEN_REQUESTS'));
        
        $okay = self::httpCodeIsOkay($http_status);

        //Sometimes HEAD requests result in a false negative.  Do a GET request just to verify
        if (true == $options[CURLOPT_NOBODY] && false === $okay) {
            $options[CURLOPT_NOBODY] = false;
            return self::getHTTPInfo($url, $options);
        }

        return array(
            'http_code'     => $http_status,
            'curl_code'     => $curl_error_no,
            'effective_url' => $effective_url,
            'okay'          => $okay
        );
    }

    /**
     * Determine if the URL is okay
     * 
     * @param $http_code
     * @return bool
     */
    public static function httpCodeIsOkay($http_code)
    {
        if ($http_code >= 200 && $http_code < 300) {
            return true;
        }
        
        return false;
    }

    /**
     * Get a page title via it's <title> tag
     * 
     * @param $url
     * @return string
     */
    public static function getPageTitle($url)
    {
        $page = @file_get_contents($url);

        if (strlen($page)) {
            $results = array();

            preg_match("/\<title\>(.*)\<\/title\>/", $page, $results);

            if (isset($results[1])) {
                return $results[1];
            }
        }

        return "unknown";
    }
    
    public static function log($level, $message, array $context = array())
    {
        static $log;
        
        if (!$log) {
            $log = new Logger('sitemaster');
            $log->pushHandler(new StreamHandler(self::getRootDir() . '/tmp/sitemaster.log'));
            $email_to = Config::get('LOG_EMAIL_TO');
            if (!empty($email_to)) {
                $log->pushHandler(new NativeMailerHandler($email_to, 'SiteMaster Alert', Config::get('LOG_EMAIL_FROM'), Config::get('LOG_EMAIL_LEVEL')));
            }
        }

        $log->log($level, $message, $context);
    }
    
    public static function getAbsoluteBaseURL()
    {
        $base = Config::get('URL');
        if (filter_var($base, FILTER_VALIDATE_URL) !== false) {
            return $base;
        }

        $port = '';
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] !== '80') {
            $port = ':' . $_SERVER['SERVER_PORT'];
        }
        
        return 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].$port.$base;
    }

    /**
     * Determine if a user-provided date is valid
     * 
     * @param $date
     * @return bool
     */
    public static function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    /**
     * Strip fragments for URIs
     *
     * This is used when getting the status code for a URI.
     * Some environments return 404 for every URI with a #fragment
     *
     * @param string $uri
     * @return string the new URI
     */
    public static function stripURIFragment($uri)
    {
        $parts = explode('#', $uri, 2);

        if (isset($parts[0])) {
            return $parts[0];
        }

        return $uri;
    }
}