<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\Registry\Registry;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
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

        //Require login
        Session::requireLogin();
        
        if (isset($this->options['recommended'])) {
            $message = new FlashBagMessage(FlashBagMessage::TYPE_INFO, 'We filled in the base URL of the site for you based on what we think it probably is.  Please change it if we did not guess right.');
            Controller::addFlashBagMessage($message);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return Config::get('URL') . 'sites/add/';

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
        if (!isset($post['base_url'])) {
            throw new UnexpectedValueException('the base url was not provided', 400);
        }
        
        $base_url = Util::validateBaseURL($post['base_url'], true);
        
        $registry = new Registry();
        
        if (false == $registry->URLIsAllowed($base_url)) {
            $allowed_domains = implode(', ', Config::get('ALLOWED_DOMAINS'));
            throw new UnexpectedValueException('The provided URL is not allowed.  It must be a site within one of the following domains: ' . $allowed_domains, 400);
        }
        
        if ($site = Site::getByBaseURL($base_url)) {
            Controller::redirect($site->getJoinURL());
        }
        
        $options = array();
        $options['title'] = Util::getPageTitle($base_url);
        
        if (!$site = Site::createNewSite($base_url)) {
            throw new RuntimeException('Unable to create the site', 500);
        }

        Controller::redirect($site->getJoinURL());
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
