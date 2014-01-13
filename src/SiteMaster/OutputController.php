<?php
namespace SiteMaster;

use SiteMaster\Events\RegisterTheme;
use SiteMaster\Plugin\PluginManager;
use SiteMaster\Plugins\Auth_Unl\RuntimeException;
use SiteMaster\Util;

class OutputController extends \Savvy
{
    public $format = 'html';
    protected $options = array();
    protected $theme = 'bootstrap';
    protected $webDir = '';

    public function __construct($options = array())
    {
        parent::__construct();
        $this->options = $options;
        $this->webDir = dirname(dirname(__DIR__)) . '/www';
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
            throw new RuntimeException('Invalid theme, there are no files in '.$dir);
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
                throw new UnexpectedValueException('Invalid/unsupported output format', 500);
        }
    }

    public function getBaseTemplatePath($format) {
        return $this->webDir . '/templates/' . $format;
    }

    /**
     * Set the array of template paths necessary for this format
     *
     * @param string $format Format to use
     */
    public function setTemplateFormatPaths($format)
    {
        $plugin_dir = dirname(dirname(__DIR__)) . '/plugins';

        $this->format = $format;

        $this->setTemplatePath(
            array(
                $this->getBaseTemplatePath($format),
                $plugin_dir,
                $this->getThemeDir($this->theme) . '/' . $format
            )
        );
    }

    /**
     * Render an object with the base theme
     * 
     * @param mixed $mixed
     * @param string $template
     * @return string
     */
    public function renderWithBase($mixed = null, $template = null)
    {
        $tmp = $this->getTemplatePath();
        $this->setTemplatePath($this->getBaseTemplatePath($this->format));
        $result = $this->render($mixed, $template);
        $this->setTemplatePath($tmp);
        return $result;
    }

    /**
     * Render an object with the theme stack
     * 
     * This is generally used within the base templates, so that if a theme uses renderWithBase(), the base theme
     * does not continue to render the entire stack with the base theme.
     * 
     * @param mixed $mixed
     * @param string $template
     * @return string
     */
    public function renderWithTheme($mixed = null, $template = null)
    {
        $this->setTemplateFormatPaths($this->format);
        return $this->render($mixed, $template);
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