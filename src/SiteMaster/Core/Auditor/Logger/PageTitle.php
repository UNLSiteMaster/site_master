<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;

use SiteMaster\Core\Auditor\Site\Page;

class PageTitle extends \Spider_LoggerAbstract
{
    /**
     * @var bool|Page
     */
    protected $page = false;

    function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {
        if (!$result = $xpath->query('//xhtml:title')) {
            echo 'return' . PHP_EOL;
            return;
        }
        
        if (!$result->length) {
            return;
        }
        
        $this->page->title = $result->item(0)->textContent;
        $this->page->save();
    }
}
