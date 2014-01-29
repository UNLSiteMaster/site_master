<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Controller;
use SiteMaster\Core\InvalidArgumentException;
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

    /**
     * @var array The roles.id for each member_roles entry
     */
    public $user_role_ids = array();


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
        
        if ($this->user_roles) {
            foreach ($this->user_roles as $role) {
                $this->user_role_ids[] = $role->roles_id;
            }
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
        $role_ids = array();
        if (isset($post['role_ids'])) {
            $role_ids = $post['role_ids'];
        }
        
        if (!is_array($role_ids)) {
            throw new InvalidArgumentException('roles_ids must be an array', 400);
        }
        
        //Find and add all 'new' roles
        $add_roles = $this->getRolesToAdd($role_ids);
        if (!empty($add_roles)) {
            if (!$this->membership) {
                $this->membership = Member::createMembership($this->user, $this->site);
            }

            $this->membership->addRoles($add_roles);
        }

        //Find and remove all 'unselected' roles
        if ($this->membership) {
            $this->membership->removeRoles($this->getRolesToRemove($role_ids));
        }
        
        Controller::redirect($this->site->getURL() . 'members/');
    }

    /**
     * @param array $role_ids
     * @return array roles.ids to add
     */
    protected function getRolesToAdd(array $role_ids)
    {
        if (!$this->user_role_ids) {
            return $role_ids;
        }

        return array_diff(
            array_values($role_ids),
            array_values($this->user_role_ids)
        );
    }

    /**
     * @param array $role_ids
     * @return array roles.ids to remove
     */
    protected function getRolesToRemove(array $role_ids)
    {
        if (!$this->user_role_ids) {
            return array();
        }
        
        return array_diff(
            array_values($this->user_role_ids),
            array_values($role_ids)
        );
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
        if (!$this->user_role_ids) {
            return false;
        }

        return in_array($role_id, $this->user_role_ids);
    }
}
