<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Query\Result;
use SiteMaster\Core\User\User;

class Query extends \IteratorIterator
{
    public $sites = array();
    public $registry;
    
    const QUERY_TYPE_ALL  = 1;
    const QUERY_TYPE_URL  = 2;
    const QUERY_TYPE_USER = 3;
    const QUERY_TYPE_URL_CONTAINS = 4;

    function __construct($options = array())
    {
        $this->registry = new Registry();
    }

    /**
     * Query for a list of sites
     * 
     * @param $query
     * @return Result
     */
    public function query($query)
    {
        $type = $this->getQueryType($query);
        $function = $this->getQueryFunction($type);
        
        $result = $this->$function($query);

        if (is_array($result)) {
            //Make sure it is traversable
            $result = new \ArrayIterator($result);
        }
        
        return new Result(array('result' => $result));
    }

    /**
     * Get the query function for a given type of query
     * 
     * @param $type
     * @return string
     */
    public function getQueryFunction($type)
    {
        switch ($type) {
            case self::QUERY_TYPE_USER:
                return 'getByUser';
            case self::QUERY_TYPE_ALL:
                return 'getByALL';
            case self::QUERY_TYPE_URL:
                return 'getByURL';
            default:
                return 'getByURLContains';
        }
    }

    /**
     * Get the query type for a given query
     * 
     * This helps to determine how to get results for the query
     * 
     * @param $query
     * @return int
     */
    public function getQueryType($query)
    {
        // will be a url with a * at the end
        if(filter_var(substr($query, 0 , -1), FILTER_VALIDATE_URL) == substr($query, 0, -1) && substr($query, -1) == '*'){
            return self::QUERY_TYPE_URL_CONTAINS;
        }

        //Determine the type of query and get the sites associated with it.
        switch ($query) {
            case filter_var($query, FILTER_VALIDATE_URL): //Get a site and it's parents.
                return self::QUERY_TYPE_URL;
            case '*': //Get a list of all sites in the registry.
                return self::QUERY_TYPE_ALL;
            default: //Get a list of sites associated with a user.
                return self::QUERY_TYPE_USER;
        }
    }

    /**
     * Get ALL sites
     * 
     * @param $query
     * @return Sites\All
     */
    public function getByALL($query)
    {
        return new Sites\All();
    }

    /**
     * Get all sites for a user
     * 
     * @param $query
     * @return array|Sites\ApprovedForUser
     * @throws \SiteMaster\InvalidArgumentException
     */
    public function getByUser($query)
    {
        $details = explode('@', $query);
        
        if (count($details) < 2) {
            throw new InvalidArgumentException('Must provide a query in the format of uid@provider');
        }

        $provider = array_pop($details);
        $uid = implode('@', $details);

        if (!$user = User::getByUIDAndProvider($uid, $provider)) {
            return array();
        }
        
        return $user->getApprovedSites();
    }

    /**
     * Get all sites for a URL
     * starting with the closest, up the chain...
     * 
     * @param $query
     * @return array
     */
    public function getByURL($query)
    {
        $sites = array();
        
        if (!$site = $this->registry->getClosestSite($query)) {
            return $sites;
        }
        
        do {
            //Handle aliases
            if (isset(Registry::$aliases[$site->base_url])
                && $alias = Site::getByBaseURL(Registry::$aliases[$site->base_url])) {
                $site = $alias;
            }
            
            $sites[] = $site;
        } while ($site = $site->getParentSite());
        
        return $sites;
    }

    /**
     * Get all sites containing a URL
     * 
     * @param $query
     * @return array
     */
    public function getByURLContains($query)
    {
        $sites = array();

        // removes * from end
        $queryStripped = substr($query, 0, -1);
        
        if (!$site = $this->registry->getSitesContaining($queryStripped)) {
            return $sites;
        }
        
        do {
            //Handle aliases
            if (isset(Registry::$aliases[$site->base_url])
                && $alias = Site::getByBaseURL(Registry::$aliases[$site->base_url])) {
                $site = $alias;
            }
            
            $sites[] = $site;
        } while ($site = $site->getParentSite());
        
        return $sites;
    }

}