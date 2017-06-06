<?php
namespace SiteMaster\Core;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class TermsOfUse implements ViewableInterface
{

    function __construct($options = array())
    {
        
    }

    public function getPageTitle()
    {
        return "Terms Of Use";
    }

    public function getURL()
    {
        return Config::get('URL') . 'terms-of-use/';
    }
}
