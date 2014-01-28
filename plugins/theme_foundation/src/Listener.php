<?php
namespace SiteMaster\Plugins\Theme_Foundation;

use SiteMaster\Core\Plugin\PluginListener;
use SiteMaster\Core\Events\RegisterTheme;
use SiteMaster\Core\Events\Theme\RegisterScripts;
use SiteMaster\Core\Events\Theme\RegisterStyleSheets;
use SiteMaster\Core\Config;

class Listener extends PluginListener
{
    /**
     * @param RegisterTheme $event
     */
    public function onRegisterTheme(RegisterTheme $event)
    {
        if ($event->getTheme() == 'foundation') {
            $event->setPlugin($this->plugin);
        }
    }

    /**
     * @param RegisterStyleSheets $event
     */
    public function onThemeRegisterStyleSheets(RegisterStyleSheets $event)
    {
        if (Config::get('THEME') != 'foundation') {
            return;
        }
        
        $event->addStyleSheet(Config::get('URL') . 'plugins/theme_foundation/www/themes/foundation/html/css/foundation.css');
    }

    /**
     * @param \SiteMaster\Core\Events\Theme\RegisterScripts $event
     */
    public function onThemeRegisterScripts(RegisterScripts $event)
    {
        if (Config::get('THEME') != 'foundation') {
            return;
        }
        
        $event->addScript(Config::get('URL') . 'plugins/theme_foundation/www/themes/foundation/html/js/modernizr.js');
    }
}