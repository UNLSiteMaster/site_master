<?php
namespace SiteMaster\Core\Auditor\Site\Page\Mark;

use SiteMaster\Core\Auditor\Site\Page\MetricGrade;
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
    public $site;

    /**
     * @var \SiteMaster\Core\Auditor\Site\Page
     */
    public $page;

    /**
     * @var \SiteMaster\Core\Auditor\Scan
     */
    public $scan;

    /**
     * @var \SiteMaster\Core\Auditor\Site\Page\Mark
     */
    public $page_mark;

    /**
     * @var false|\SiteMaster\Core\Auditor\Metric\Mark
     */
    public $mark;

    /**
     * @var MetricGrade
     */
    public $metric_grade;

    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new InvalidArgumentException('a site id is required', 400);
        }

        if (!isset($this->options['page_marks_id'])) {
            throw new InvalidArgumentException('a page mark id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find a site with the given id', 400);
        }

        if (!$this->page_mark = Page\Mark::getByID($this->options['page_marks_id'])) {
            throw new InvalidArgumentException('Could not find a page with the given id.', 404);
        }

        if (!$this->page = $this->page_mark->getPage()) {
            throw new InvalidArgumentException('Could not find a page with the given id.', 404);
        }

        if (!$this->scan = $this->page->getScan()) {
            throw new InvalidArgumentException('Could not find a scan for the given page.', 500);
        }

        if (!$this->mark = $this->page_mark->getMark()) {
            throw new InvalidArgumentException('Could not find a scan for the given page.', 500);
        }

        $this->metric_grade = MetricGrade::getByMetricIDAndScannedPageID($this->mark->metrics_id, $this->page->id);
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->page->getURL() . 'marks/' . $this->page_mark->id . '/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'How to fix ' . $this->mark->name;
    }
}
