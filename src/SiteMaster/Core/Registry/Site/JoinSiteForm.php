<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Registry\Site;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class JoinSiteForm implements ViewableInterface, PostHandlerInterface
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
     * @var bool|\SiteMaster\Core\User\
     */
    public $user = false;

    /**
     * @var bool|Member
     */
    public $membership = false;

    /**
     * @var bool|\SiteMaster\Core\Registry\Site\Member\Roles\All
     */
    public $user_roles = false;

    /**
     * @var bool|\SiteMaster\Core\Registry\Site\Roles\All
     */
    public $all_roles = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();
        
        //get the site
        if (!isset($this->options['site_id'])) {
            throw new \InvalidArgumentException('a site id is required', 400);
        }
        
        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new \InvalidArgumentException('Could not find that site', 400);
        }

        $this->user = Session::getCurrentUser();
        $this->all_roles = new Roles\All();
        $this->membership = Member::getByUserIDAndSiteID($this->user->id, $this->site->id);
        
        if ($this->membership) {
            $this->user_roles = $this->membership->getRoles();
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getJoinURL();

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Join ' . $this->site->base_url;
    }

    public function handlePost($get, $post, $files)
    {

    }

    public function getEditURL()
    {
        return $this->getURL();
    }

    /**
     * Determines if the user currently has a specific role for this site
     * 
     * @param $role_id
     * @return bool
     */
    public function userHasRole($role_id)
    {
        if (!$this->user_roles) {
            return false;
        }
        
        $roles = $this->user_roles->getInnerIterator();
        
        return isset($roles[$role_id]);
    }
}
