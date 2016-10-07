<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\Util;

class Links extends \Spider_LoggerAbstract
{
    /**
     * @var \Spider
     */
    protected $spider;

    /**
     * @var Page
     */
    protected $page;

    function __construct(\Spider $spider, Page $page)
    {
        $this->spider = $spider;
        $this->page   = $page;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {

        $links = \Spider::getUris(\Spider::getUriBase($uri), $uri, $xpath);
        
        $filters = array(
            '\\SiteMaster\\Core\\Auditor\\Filter\\Scheme',
            '\\SiteMaster\\Core\\Auditor\\Filter\\InvalidURI',
        );

        //Filter the links
        foreach ($filters as $filter_class) {
            $links = new $filter_class($links);
        }

        $links_array = array();
        foreach ($links as $link) {
            $links_array[] = Util::stripURIFragment($link);
        }

        $total_scanned = 0;
        foreach ($links_array as $uri) {
            $insert_only_if_cached = false;
            if ($total_scanned >= Config::get('LINK_SCAN_LIMIT')) {
                $insert_only_if_cached = true;
            }
            
            //Log the link
            $link = Page\Link::createNewPageLink($this->page->id, $uri, [], $insert_only_if_cached);

            //Increase count if was not cached
            if ($link && !$link->cached) {
                $total_scanned++;
            }
        }
        
        if ($total_scanned >= Config::get('LINK_SCAN_LIMIT')) {
            $this->page->link_limit_hit = Page::LIMIT_LIMIT_HIT_YES;
            $this->page->save();
        }
    }
}
