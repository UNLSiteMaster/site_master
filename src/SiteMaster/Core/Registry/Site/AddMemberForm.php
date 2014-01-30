<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\RequiredLoginException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class AddMemberForm implements ViewableInterface, PostHandlerInterface
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
    public $user = false;
    /**
     * @var bool|Member
     */
    public $membership = false;


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

        if (!$this->user = Session::getCurrentUser()) {
            throw new RequiredLoginException('You must be logged in to access this', 401);
        }

        $this->membership = Member::getByUserIDAndSiteID($this->user->id, $this->site->id);
        
        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to access this', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'members/add/';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Add a member to ' . $this->site->base_url;
    }

    public function handlePost($get, $post, $files)
    {
        
    }

    /**
     * Determine if this user can edit members
     *
     * This includes Verifying, add/remove/approving roles
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->user) {
            return false;
        }

        if ($this->user->isAdmin()) {
            return true;
        }

        if (!$this->membership) {
            return false;
        }

        if ($this->membership->isVerified()) {
            return true;
        }

        return false;
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}