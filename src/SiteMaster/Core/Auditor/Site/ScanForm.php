<?php
namespace SiteMaster\Core\Auditor\Site;

use SiteMaster\Core\AccessDeniedException;
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

class ScanForm implements ViewableInterface, PostHandlerInterface
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
        return $this->site->getURL() . 'scan/';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Scan Site';
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

    public function handlePost($get, $post, $files)
    {
        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to schedule a scan', 403);
        }
        
        if (!isset($post['action'])) {
            throw new InvalidArgumentException('An action must be specified', 400);
        }

        switch ($post['action']) {
            case 'scan':
                $this->scan($get, $post, $files);
                break;
            default:
                throw new InvalidArgumentException('An invalid action was given', 400);
        }
    }

    /**
     * handle the edit post action
     *
     * @param $get
     * @param $post
     * @param $files
     */
    protected function scan($get, $post, $files)
    {
        if ($this->site->scheduleScan(Scan::SCAN_TYPE_USER)) {
            Controller::redirect(
                $this->site->getURL(),
                new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'A new scan has been scheduled.  Feel free to grab some coffee or work on something else -- we will email you if we find any changes.')
            );
        } else {
            Controller::redirect(
                $this->site->getURL(),
                new FlashBagMessage(FlashBagMessage::TYPE_ERROR, 'Can\'t schedule a scan because there is already one scheduled')
            );
        }
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
