<?php
namespace SiteMaster\Plugins\Example;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class Example implements ViewableInterface
{
    function __construct($options = array())
    {

    }

    public function getURL()
    {
        return Config::get('URL') . 'example/';
    }

    public function getPageTitle()
    {
        return "Example";
    }
}