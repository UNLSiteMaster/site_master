<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class MembersForm implements ViewableInterface, PostHandlerInterface
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
     * @var bool|\SiteMaster\Core\Registry\Site\Member\Roles\Pending
     */
    public $pending = false;

    /**
     * @var bool|\SiteMaster\Core\Registry\Site\Member\Roles\Pending
     */
    public $members = false;

    /**
     * @var bool|Member
     */
    public $membership = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new \InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new \InvalidArgumentException('Could not find that site', 400);
        }

        if ($this->user = Session::getCurrentUser()) {
            $this->membership = Member::getByUserIDAndSiteID($this->user->id, $this->site->id);
        }
        
        $this->pending  = new Member\Roles\Pending(array('site_id'=>$this->site->id));
        $this->members = new Members\All(array('site_id'=>$this->site->id));
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'members/';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Members of ' . $this->site->base_url;
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
