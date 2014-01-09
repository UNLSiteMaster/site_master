<?php
namespace SiteMaster\Registry;

use \SiteMaster\Config;
use \SiteMaster\ViewableInterface;

class Search implements ViewableInterface
{
    function __construct($options = array())
    {

    }

    public function getURL()
    {
        return Config::get('URL') . 'registry/';
    }

    public function getPageTitle()
    {
        return "Registry";
    }
}