<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Config;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugins\Auth_Unl\RuntimeException;
use SiteMaster\Core\Util;

class Registry
{
    /**
     * An array of sites to be aliased in key=>value pairs.
     * the key is the base url to alias, the value is the base url that you want the system to return
     *
     * Note: it should be discouraged to use this in practice
     * 
     * array('from'=>'to');
     * @var array
     */
    public static $aliases = array();

    /**
     * Get an array of possible base uris for a given uri
     * 
     * @param $uri
     * @return array
     * @throws \SiteMaster\InvalidArgumentException
     */
    public function getPossibleSiteURIs($uri)
    {
        $uris = array();

        $parts = parse_url($uri);

        if (!isset($parts['host'])) {
            throw new InvalidArgumentException('Invalid url ' . $uri);
        }

        if (!isset($parts['path'])) {
            $parts['path'] = '/';
        }

        $parts['path'] = $this->trimFileName($parts['path']);

        $paths = explode('/',$parts['path']);

        $total_dirs = count($paths);

        //Loop over the paths (starting from the last path) to find the closest site.
        for ($i=$total_dirs-1; $i>=0; $i--) {
            $path = implode('/',$paths);
            
            //Add on a trailing slash if we need it.
            if (substr($path, -1) != '/') {
                $path .= '/';
            }

            $uris[] = 'http%://' . $parts['host'] . $path;

            unset($paths[$i]);
        }
        
        //Make sure that we only have unique values
        $uris = array_unique($uris);
        
        //Make sure that they are indexed correctly if array_unique removed any
        return array_values($uris);
    }

    /**
     * Return the Root URL of a URL
     *
     * A Root URL is defined as the root URL with only a '/' for the path
     *
     * @param $url
     * @return string the base URL with http%:// as the protocol
     */
    public function getRootURL($url)
    {
        $possible_site_uris = $this->getPossibleSiteURIs($url);
        
        return array_pop($possible_site_uris);
    }

    /**
     * Determine if the given URL is allowed in the system
     *
     * @param $url
     * @return bool
     */
    public function URLIsAllowed($url)
    {
        $allowed_domains = Config::get('ALLOWED_DOMAINS');
        
        if (empty($allowed_domains)) {
            return true;
        }

        $parts = parse_url($url);
        
        if (!isset($parts['host'])) {
            return false;
        }

        $regex = "";
        foreach ($allowed_domains as $domain) {
            $regex .= preg_quote($domain, ".-/") . "|";
        }

        $regex = trim($regex, "|");
        if (preg_match("/" . $regex . "$/", $parts['host'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Trim a filename from a given path
     * 
     * @param $path
     * @return string - the path without a filename
     */
    public function trimFileName($path)
    {
        $parts = explode('/', $path);
        
        $filename = array_pop($parts);
        
        //if the last character of the path was '/', $filename will be empty.
        if ($filename == '') {
            //No filename to trim, so return early
            return $path;
        }
        
        $location = strrpos($path, $filename);
        
        return substr_replace($path, '', $location);
    }

    /**
     * Get the sql to get the closest site for a set of URIs
     * This method generates a prepared statement for mysqli
     * 
     * Note, this method exists mostly for testing
     * 
     * @param $possible_uris
     * @return string
     */
    public function getClosestSiteSQL($possible_uris)
    {
        $sql = "SELECT id FROM " . Site::getTable() . PHP_EOL;
        $sql .= "WHERE" . PHP_EOL;
        
        foreach ($possible_uris as $uri) {
            $sql .= ' base_url LIKE ? OR ' . PHP_EOL;
        }

        $sql = substr($sql, 0, -5) . PHP_EOL . 'ORDER BY SUBSTRING_INDEX( base_url, \'://\', -1 ) DESC LIMIT 1';
        
        return $sql;
    }

    /**
     * Get the closest site for a given uri
     *
     * @param $uri
     * @throws \SiteMaster\Plugins\Auth_Unl\RuntimeException
     * @return bool|Site
     */
    public function getClosestSite($uri)
    {
        $possible_uris = $this->getPossibleSiteURIs($uri);
        
        $sql = $this->getClosestSiteSQL($possible_uris);
        
        $mysqli = Util::getDB();
        
        $stmt = $mysqli->prepare($sql);

        $values = array();
        $values[0] = '';
        foreach ($possible_uris as $key=>$uri) {
            $values[0] .= 's';
            $values[] = &$possible_uris[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), $values);

        $stmt->bind_result($id);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error executing mysqli statement ' . $stmt->error);
        }
        
        if (!$stmt->fetch()) {
            return false;
        }
        
        if (is_null($id)) {
            return false;
        }

        $stmt->close();
        
        return Site::getByID($id);
    }

    /**
    * Get the sql to get the any site containing that URIs
    * This method generates a prepared statement for mysqli
    * 
    * Note, this method exists mostly for testing
    * 
    * @param $possible_uris
    * @return string
    */
    public function getSiteContainingSQL()
    {
        $sql = "SELECT id FROM " . Site::getTable() . PHP_EOL;
        $sql .= "WHERE" . PHP_EOL;

        $sql .= ' base_url LIKE ? ' . PHP_EOL;

        $sql .= 'ORDER BY SUBSTRING_INDEX( base_url, \'://\', -1 ) DESC';
        
        return $sql;
    }

    /**
    * Get the site containing a given URI
    *
    * @param $uri
    * @throws \SiteMaster\Plugins\Auth_Unl\RuntimeException
    * @return array
    */
    public function getSitesContaining($uri)
    {
        // uses the getPossibleSiteURIs and takes the first one to be used
        // the first one will be the original URI
        // this function will format the http part nicely 
        $possible_uris = $this->getPossibleSiteURIs($uri);

        // we will add the wild card at the end to get any children sites
        $formatted_uri = $possible_uris[0] . "%";
        
        // creates the SQL
        $sql = $this->getSiteContainingSQL();

        $mysqli = Util::getDB();
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $formatted_uri);
        $stmt->bind_result($id);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error executing mysqli statement ' . $stmt->error);
        }

        // we get all the ids
        // we can not get the site since the $stmt connection is still open
        $fetched_ids = array();
        while ($stmt->fetch()) {
            if (is_null($id)) {
                continue;
            }

            $fetched_ids[] = $id;
        }

        $stmt->close();

        // we do this loop to get the site and validate it
        $fetched_sites = array();
        foreach ($fetched_ids as $id) {
            $site = Site::getByID($id);
            if ($site === false) {
                continue;
            }
            $fetched_sites[] = $site;
        }

        return $fetched_sites;
    }

    /**
     * return the recommended base URL, which is usually the closest folder
     *
     * @param string $uri the absolute url
     * @return mixed
     */
    public function getRecommendedBaseURL($uri)
    {
        $registry = new Registry();
        $possibilities = $registry->getPossibleSiteURIs($uri);

        // This should return the closest site (most specific)
        if (!empty($possibilities)) {
            //Keep the same scheme
            $scheme = parse_url($uri, PHP_URL_SCHEME);
            return str_replace('http%://', $scheme.'://', $possibilities[0]);
        }
        
        return $uri . '/';
    }
}
