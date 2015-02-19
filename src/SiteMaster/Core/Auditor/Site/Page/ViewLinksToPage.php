<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Site\Page;

class ViewLinksToPage implements ViewableInterface
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
     * @var bool|\SiteMaster\Core\Auditor\Site\Page
     */
    public $page = false;

    /**
     * @var bool|\SiteMaster\Core\Auditor\Scan
     */
    public $scan = false;

    /**
     * @var bool|Links\ForScanAndURL
     */
    public $links = false;

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

        if (!$this->page = Page::getByID($this->options['pages_id'])) {
            throw new InvalidArgumentException('Could not find a page with the given id.', 404);
        }

        if (!$this->scan = $this->page->getScan()) {
            throw new InvalidArgumentException('Could not find a scan for the given page.', 500);
        }
        
        $this->links = $this->page->getLinksToThisPage();
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->page->getURL() . 'links-to-this/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Links to Scanned Page - ' . $this->page->uri;
    }
}
