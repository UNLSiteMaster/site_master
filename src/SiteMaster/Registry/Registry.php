<?php
namespace SiteMaster\Registry;

use SiteMaster\InvalidArgumentException;
use SiteMaster\Plugins\Auth_Unl\RuntimeException;
use SiteMaster\Util;

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
        $sql = "SELECT * FROM " . Site::getTable() . PHP_EOL;
        $sql .= "WHERE" . PHP_EOL;
        
        foreach ($possible_uris as $uri) {
            $sql .= ' base_url LIKE ? OR ' . PHP_EOL;
        }

        $sql = substr($sql, 0, -5) . PHP_EOL . 'ORDER BY base_url DESC LIMIT 1';
        
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

        if (!$stmt->execute()) {
            throw new RuntimeException('Error executing mysqli statement ' . $stmt->error);
        }
        
        if (!$result = $stmt->get_result()) {
            return false;
        }

        $stmt->close();
        
        if (!$result->num_rows) {
            return false;
        }

        $site = new Site();
        $site->synchronizeWithArray($result->fetch_array());
        
        return $site;
    }
}
