<?php
namespace SiteMaster\Registry;

use SiteMaster\InvalidArgumentException;

class Registry
{
    public static $aliases = array();
    
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

        $dirs = count($paths);

        //Loop over the paths (starting from the last path) to find the closest site.
        for ($i=$dirs-1; $i>=0; $i--) {
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
    
    public function getClosestSite($uri)
    {
        foreach ($this->getPossibleSiteURIs($uri) as $possible_uri) {
            if ($site = Site::getByBaseURL($possible_uri)) {
                return $site;
            }
        }
        
        return false;
    }
}