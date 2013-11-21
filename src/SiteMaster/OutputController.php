<?php
namespace SiteMaster;

use SiteMaster\Events\RegisterTheme;
use SiteMaster\Plugin\PluginManager;
use SiteMaster\Util;

class OutputController extends \Savvy
{
    public $format = 'html';
    protected $options = array();
    protected $theme = 'bootstrap';

    public function __construct($options = array())
    {
        parent::__construct();
        $this->options = $options;
    }

    /**
     * Set a specific theme for this instance
     *
     * @param string $theme Theme name, which corresponds to a directory in www/
     *
     * @throws Exception
     */
    function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param $theme
     * @throws Exception
     * @return string - the absolute path to the theme directory
     */
    function getThemeDir($theme)
    {
        $event = PluginManager::getManager()->dispatchEvent(
            RegisterTheme::EVENT_NAME,
            new RegisterTheme($theme)
        );

        $dir = Util::getRootDir() . '/www/themes/' . $theme;

        if ($plugin = $event->getPlugin()) {
            $dir = $plugin->getRootDirectory() . '/www/themes/' . $theme;
        }

        if (!is_dir($dir)) {
            throw new Exception('Invalid theme, there are no files in '.$dir);
        }

        return $dir;
    }

    public function initialize()
    {
        switch ($this->options['format']) {
            case 'html':
                // Always escape output, use $context->getRaw('var'); to get the raw data.
                $this->setEscape(function($data) {
                    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', false);
                });
                header('Content-type:text/html;charset=UTF-8');
                $this->setTemplateFormatPaths('html');
                break;
            default:
                throw new Exception('Invalid/unsupported output format', 500);
        }
    }

    /**
     * Set the array of template paths necessary for this format
     *
     * @param string $format Format to use
     */
    public function setTemplateFormatPaths($format)
    {
        $web_dir = dirname(dirname(__DIR__)) . '/www';
        $plugin_dir = dirname(dirname(__DIR__)) . '/plugins';

        $this->format = $format;

        $this->setTemplatePath(
            array(
                $web_dir . '/templates/' . $format,
                $plugin_dir,
                $this->getThemeDir($this->theme) . '/' . $format
            )
        );
    }


    public function findTemplateFile($file)
    {

        //take of the plugin namespace
        $tmp = str_replace("SiteMaster/Plugins/", "", $file, $count);

        //if the plugin namespace wasn't found... continue normally
        if (!$count) {
            return parent::findTemplateFile($file);
        }

        $parts = explode("/", $tmp);

        //If there is nothing after the plugin, don't continue.
        if (!$plugin = array_shift($parts)) {
            return false;
        }

        //start the starting directory (plugin/src/) for plugin classes
        $file = strtolower($plugin) . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->format . DIRECTORY_SEPARATOR;

        //convert the namespace to a path
        $file .=  implode(DIRECTORY_SEPARATOR, $parts);

        return parent::findTemplateFile($file);
    }
}