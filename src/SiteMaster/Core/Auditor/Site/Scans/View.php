<?php
namespace SiteMaster\Core\Auditor\Site\Scans;

use SiteMaster\Core\Auditor\Scans\AllForSite;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Site\Page;

class View implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var \SiteMaster\Core\Registry\Site
     */
    public $site = false;

    /**
     * @var AllForSite
     */
    public $scans;

    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find a site with the given id', 400);
        }

        $this->scans = new AllForSite(array('sites_id'=>$this->site->id));
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'scans/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Scans for ' . $this->site->getTitle();
    }
}
