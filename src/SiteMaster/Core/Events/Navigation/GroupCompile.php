<?php
namespace SiteMaster\Core\Events\Navigation;

use Symfony\Component\EventDispatcher\Event;

class GroupCompile extends Event
{
    const EVENT_NAME = 'navigation.group.compile';

    protected $navigation = [];
    
    protected $group_name;

    public function __construct($group_name)
    {
        $this->group_name = $group_name;
    }
    
    public function getGroupName()
    {
        return $this->group_name;
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