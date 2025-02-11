<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\AbstractPostHandler;
use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\CSRFValidationException;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\PathRequiredException;
use SiteMaster\Core\Registry\Registry;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ValidationMessage;
use SiteMaster\Core\ViewableInterface;

class AddSiteForm extends AbstractPostHandler implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var array an array of errors, values are element IDs
     */
    public $errors = array();


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();
        
        if (isset($this->options['recommended'])) {
            $message = new FlashBagMessage(FlashBagMessage::TYPE_INFO, 'We filled in the base URL of the site for you based on what we think it probably should be.  Please change it if we did not guess right.');
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
        if (!Controller::getCSRFHelper()->validateRequest()) {
            throw new CSRFValidationException();
        }
        
        if (!isset($post['base_url'])) {
            throw new UnexpectedValueException('the base url was not provided', 400);
        }

        $registry = new Registry();
        
        try {
            $base_url = Util::validateBaseURL($post['base_url'], true);
        } catch (PathRequiredException $e) {
            
            $this->addError('base_url', 'It looks like you gave an invalid base url; it must end in a slash.  We replaced it with our best guess, please make sure it is correct and submit the form again.');

            $this->options['recommended'] = $registry->getRecommendedBaseURL($post['base_url']);
            
            //stop rendering
            return false;
        } catch (InvalidArgumentException $e) {
            $this->addError('base_url', $e->getMessage());

            //stop rendering
            return false;
        }
        
        if (false == $registry->URLIsAllowed($base_url)) {
            $allowed_domains = implode(', ', Config::get('ALLOWED_DOMAINS'));
            
            $this->addError('base_url', 'The provided URL is not allowed.  It must be a site within one of the following domains: ' . $allowed_domains);
            
            //stop rendering
            return false;
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
        if (isset($_GET['recommended']) && !empty($_GET['recommended'])) {
            return $this->getURL() . "?recommended=" . urlencode($_GET['recommended']);
        }

        return $this->getURL();
    }
}
