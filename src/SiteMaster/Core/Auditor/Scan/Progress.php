<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Auditor\Scan;

class Progress extends View
{
    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->scan->getURL() . 'progress/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Site Scan Progress';
    }

    /**
     * Get the percent complete for this scan
     * 
     * @return int - the percentage
     */
    public function getProgressPercent()
    {
        $progress = 0;
        
        $page_count = $this->scan->getDistinctPageCount();
        
        $scanned_page_count = $this->scan->getDistinctFinishedCount();
        
        if (!$page_count) {
            return $progress;
        }
        
        return floor(($scanned_page_count / $page_count) * 100);
    }

    /**
     * Get the queue position for this scan.  Because it is a queue of pages, not scans, return the position for the homepage.
     * 
     * @return bool|mixed
     */
    public function getQueuePosition()
    {
        if ($this->scan->isComplete()) {
            return false;
        }

        $site = $this->scan->getSite();
        
        if (!$homepage = Page::getByScanIDAndURI($this->scan->id, $site->base_url)) {
            return false;
        }

        $queue = new Queued(array('limit'=>-1));
        
        return array_search($homepage->id, $queue->getInnerIterator()->getArrayCopy());
    }
}
