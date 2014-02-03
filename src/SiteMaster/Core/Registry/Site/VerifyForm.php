<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;
use SiteMaster\Core\User\User;

class VerifyForm implements ViewableInterface, PostHandlerInterface
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

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $verify_user = false;

    /**
     * The membership for the current_user
     * 
     * @var bool|Member
     */
    public $current_user_membership = false;

    /**
     * The membership for the verify_user
     *
     * @var bool|Member
     */
    public $verify_user_membership = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();

        $this->setCurrentUser();
        
        $this->setVerifyUser();

        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to join this user', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        $url = $this->site->getURL() . 'verify/';
        if ($this->verify_user->id != $this->current_user->id) {
            $url .= $this->verify_user->id . '/';
        }
        
        return $url;
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Verify Membership for ' . $this->verify_user->getName();
    }

    /**
     * Set the current site
     * 
     * @throws \SiteMaster\Core\InvalidArgumentException
     */
    protected function setSite() {
        if (!isset($this->options['site_id'])) {
            throw new InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find that site', 400);
        }
    }

    /**
     * Set the current user
     */
    protected function setCurrentUser()
    {
        $this->current_user = Session::getCurrentUser();
        $this->current_user_membership = Member::getByUserIDAndSiteID($this->current_user->id, $this->site->id);
    }

    /**
     * Set the verify User
     * 
     * @throws \SiteMaster\Core\InvalidArgumentException
     */
    protected function setVerifyUser()
    {
        //Set the verify_user
        if (isset($this->options['users_id']) && !empty($this->options['users_id'])) {
            if (!$this->verify_user = User::getByID($this->options['users_id'])) {
                throw new InvalidArgumentException('Could not find that user', 400);
            }
            $this->verify_user_membership = Member::getByUserIDAndSiteID($this->verify_user->id, $this->site->id);
        } else {
            $this->verify_user = $this->current_user;
            $this->verify_user_membership = $this->current_user_membership;
        }

        if (!$this->verify_user_membership = Member::getByUserIDAndSiteID($this->verify_user->id, $this->site->id)) {
            throw new InvalidArgumentException('Could not find a membership to verify', 400);
        }

        if ($this->verify_user_membership->isVerified()) {
            throw new InvalidArgumentException('That membership is already verified', 400);
        }
    }

    /**
     * Determine if the current_user can verify the verify_user
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->current_user) {
            //no current user set
            return false;
        }

        if (!$this->verify_user) {
            //No join user set
            return false;
        }

        if ($this->current_user->isAdmin()) {
            //admin can join anyone
            return true;
        }

        if ($this->current_user->id == $this->verify_user->id) {
            //The current user can verify their self
            return true;
        }

        if (!$this->current_user_membership) {
            //The current user needs a membership if they want to verify someone else.
            return false;
        }

        if ($this->current_user_membership->isVerified()) {
            //The current user needs to be verified to verify someone else
            return true;
        }

        //Default to false
        return false;
    }

    /**
     * Determine if the current user can bypass the manual verify step
     * 
     * @return bool
     */
    public function canBypassManualVerification()
    {
        if ($this->current_user->isAdmin()) {
            //admin can join anyone
            return true;
        }

        if (!$this->current_user_membership) {
            //The current user needs a membership if they want to verify someone else.
            return false;
        }

        if ($this->current_user_membership->isVerified()) {
            //The current user needs to be verified to verify someone else
            return true;
        }
        
        return false;
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['type'])) {
            throw new InvalidArgumentException('a verification type must be provided', 400);
        }
        
        switch ($post['type']) {
            case 'manual':
                $this->manuallyVerify();
                break;
            case 'bypass':
                $this->bypassVerify();
                break;
            default:
                throw new UnexpectedValueException('That type is not supported', 400);
        }

        $notice = new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, $this->verify_user->getName() . ' has been verified');
        Controller::redirect($this->site->getURL() . 'members/', $notice);
    }
    
    protected function manuallyVerify()
    {
        $result = Util::getHTTPInfo($this->getVerificationURL());
        if (!$result['okay']) {
            throw new RuntimeException('Unable to find the verification file.  Please make sure it is present and try again.', 400);
        }
        
        $this->verify_user_membership->verify();
    }

    protected function bypassVerify()
    {
        if (!$this->canBypassManualVerification()) {
            throw new AccessDeniedException('You do not have permission to bypass verification');
        }

        $this->verify_user_membership->verify();
    }

    public function getEditURL()
    {
        return $this->getURL();
    }

    public function getVerificationURL()
    {
        return $this->site->base_url . 'sitemaster_v_' . $this->verify_user_membership->verification_code . '.txt';
    }
}
