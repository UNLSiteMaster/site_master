<?php
namespace SiteMaster\User;

use \SiteMaster\Config;
use \SiteMaster\ViewableInterface;

class Logout implements ViewableInterface
{
    function __construct($options = array())
    {
        Session::logOut();
    }

    public function getURL()
    {
        return Config::get('URL') . 'logout/';
    }

    public function getPageTitle()
    {
        return "Logout";
    }
}