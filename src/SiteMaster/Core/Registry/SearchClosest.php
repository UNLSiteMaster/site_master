<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class SearchClosest implements ViewableInterface
{
    public $query = '';

    function __construct($options = array())
    {
        if (isset($options['query'])) {
            $this->query = $options['query'];
        }
    }

    public function getPageTitle()
    {
        return "Closest Site";
    }

    public function getURL()
    {
        return Config::get('URL') . 'registry/closest/';
    }
    
    public function getSite()
    {
        if (empty($this->query)) {
            return false;
        }
        
        $registry = new Registry();
        return $registry->getClosestSite($this->query);
    }
}