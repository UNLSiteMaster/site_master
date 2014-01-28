<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class AddSiteForm implements ViewableInterface, PostHandlerInterface
{
    /**
     * @var array
     */
    public $options = array();


    function __construct($options = array())
    {
        $this->options += $options;
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return Config::get('URL') . 'sites/add';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Add a Site';
    }

    public function handlePost($get, $post, $files)
    {
        // TODO: Implement handlePost() method.
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
