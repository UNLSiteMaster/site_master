<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Scan;

class View implements ViewableInterface, \Savvy_Turbo_CacheableInterface
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
        if (isset($this->options['scan'])) {
            $this->scan = $this->options['scan'];
        } else {
            //Try to get it by ID
            if (!isset($this->options['scans_id'])) {
                throw new InvalidArgumentException('a scan id is required', 400);
            }

            if (!$this->scan = Scan::getByID($this->options['scans_id'])) {
                throw new InvalidArgumentException('Could not find a scan for the given page.', 500);
            }
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
        return $this->scan->getURL();
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Site Scan';
    }

    public function getCacheKey()
    {
        if (!$this->scan->isComplete()) {
            //Don't cache if the scan is not complete
            return false;
        }

        $key = array();
        $key['fields'] = $this->scan->getFields();
        $key['format'] = $this->options['format'];
        return serialize($key);
    }

    public function run()
    {
        return;
    }

    public function preRun($cached)
    {
        return;
    }
}
