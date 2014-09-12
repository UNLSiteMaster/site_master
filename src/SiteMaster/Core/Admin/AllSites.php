<?php
namespace SiteMaster\Core\Admin;

use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Registry\Sites\All;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Scan;

class AllSites implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var All
     */
    public $sites = false;

    function __construct($options = array())
    {
        $this->options += $options;

        $this->sites = new All();
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return \SiteMaster\Core\Config::get('URL') . 'admin/sites/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'All Sites';
    }
}
