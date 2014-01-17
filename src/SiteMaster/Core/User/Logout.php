<?php
namespace SiteMaster\Core\User;

use \SiteMaster\Core\Config;
use \SiteMaster\Core\ViewableInterface;

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