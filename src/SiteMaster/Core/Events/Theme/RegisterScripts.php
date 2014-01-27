<?php
namespace SiteMaster\Core\Events\Theme;

use Symfony\Component\EventDispatcher\Event;

class RegisterScripts extends Event
{
    const EVENT_NAME = 'themes.register.scripts';

    public $scripts = array();

    /**
     * Register a script
     * 
     * @param $url
     * @param string $type
     */
    public function addScript($url, $type = 'text/javascript')
    {
        $this->scripts[$url] = $type;
    }

    /**
     * Get the registered scripts
     * 
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }
}