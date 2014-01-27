<?php
namespace SiteMaster\Core\Events\Theme;

use Symfony\Component\EventDispatcher\Event;

class RegisterStyleSheets extends Event
{
    const EVENT_NAME = 'themes.register.stylesheets';

    public $style_sheets = array();

    /**
     * Register a style sheet
     * 
     * @param $url
     * @param string $media
     */
    public function addStyleSheet($url, $media = 'all')
    {
        $this->style_sheets[$url] = $media;
    }

    /**
     * Get the registered style sheets
     * 
     * @return array
     */
    public function getStyleSheets()
    {
        return $this->style_sheets;
    }
}