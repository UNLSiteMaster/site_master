<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use Github\Exception\MissingArgumentException;
use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Override;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class ViewOverrides implements ViewableInterface, PostHandlerInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var \SiteMaster\Core\Registry\Site
     */
    public $site = false;

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $current_user = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find that site', 400);
        }

        $this->current_user = Session::getCurrentUser();
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'overrides/';
    }

    /**
     * A user must be verified to edit a site's details
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->site) {
            return false;
        }

        if (!$this->current_user) {
            return false;
        }

        if ($this->current_user->isAdmin()) {
            return true;
        }

        if ($this->site->userIsVerified($this->current_user)) {
            return true;
        }

        if (!$membership = $this->site->getMembershipForUser($this->current_user)) {
            return false;
        }

        $roles = $membership->getRoles();

        foreach ($roles as $role) {
            if ($role->isApproved()) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Overrides for this site';
    }
    
    public function getOverrides()
    {
        return new AllForSite(['sites_id'=>$this->site->id]);
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['delete_id'])) {
            throw new MissingArgumentException('No override id was given to delete', 400);
        }
        
        if (!$this->canEdit()) {
            throw new MissingArgumentException('You do not have permission to edit this', 400);
        }
        
        if (!$override = Override::getByID($post['delete_id'])) {
            throw new MissingArgumentException('We could not find the record you indicated', 400);
        }
        
        $override->delete();

        Controller::redirect(
            $this->getURL(),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Override deleted')
        );
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
