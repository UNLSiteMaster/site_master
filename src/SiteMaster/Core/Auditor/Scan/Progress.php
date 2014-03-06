<?php
namespace SiteMaster\Core\Auditor\Scan;

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
        
        return round(($scanned_page_count / $page_count) * 100);
    }
}
