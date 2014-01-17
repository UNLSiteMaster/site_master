<?php
namespace SiteMaster\Core\Home;

use \SiteMaster\Core\Config;
use \SiteMaster\Core\ViewableInterface;

class Home implements ViewableInterface
{
    function __construct($options = array())
    {

    }

    public function getURL()
    {
        return Config::get('URL');
    }

    public function getPageTitle()
    {
        return "Welcome";
    }
}