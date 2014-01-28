<?php
namespace SiteMaster\Core\Events\Navigation;

use Symfony\Component\EventDispatcher\Event;

class SubCompile extends Event
{
    const EVENT_NAME = 'navigation.sub.compile';

    public $navigation = array();
    
    protected $for = false;

    /**
     * @param $for - the primary main nav url that this sub navigation is for
     */
    public function __construct($for)
    {
        $this->for = $for;
    }
    
    public function isFor($for)
    {
        if ($this->for != $for) {
            return false;
        }
        
        return true;
    }
    
    public function getFor()
    {
        return $this->for;
    }

    public function getNavigation()
    {
        return $this->navigation;
    }

    public function addNavigationItem($url, $title)
    {
        $this->navigation[$url] = $title;
    }
}