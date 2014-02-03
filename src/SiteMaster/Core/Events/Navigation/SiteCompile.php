<?php
namespace SiteMaster\Core\Events\Navigation;

use SiteMaster\Core\Registry\Site;

class SiteCompile extends \Symfony\Component\EventDispatcher\Event
{
    const EVENT_NAME = 'navigation.site.compile';

    public $navigation = array();
    
    protected $site = false;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * @return bool|Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return array
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * @param $url
     * @param $title
     */
    public function addNavigationItem($url, $title)
    {
        $this->navigation[$url] = $title;
    }
}