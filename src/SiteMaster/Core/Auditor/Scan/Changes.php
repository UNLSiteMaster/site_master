<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Scan;

class Changes implements ViewableInterface
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
     * @var bool|\SiteMaster\Core\Auditor\Scan
     */
    public $scan = false;

    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['scans_id'])) {
            throw new InvalidArgumentException('a scan id is required', 400);
        }

        if (!$this->scan = Scan::getByID($this->options['scans_id'])) {
            throw new InvalidArgumentException('Could not find a scan for the given page.', 500);
        }

        if (!$this->site = $this->scan->getSite()) {
            throw new InvalidArgumentException('Could not find a site with the given id', 400);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->scan->getURL() . 'changes/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Changes';
    }
}
