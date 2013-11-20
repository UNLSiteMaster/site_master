<?php
namespace SiteMaster\Home;

use \SiteMaster\Config;
use \SiteMaster\ViewableInterface;

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
        return "Chat";
    }
}