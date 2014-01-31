<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Controller;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\User\User;
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
     * @var bool|User
     */
    public $join_user = false;

    /**
     * @var bool|User
     */
    public $current_user = false;

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

        //Set the current user
        $this->current_user = Session::getCurrentUser();
        
        //Set the join_user
        if (isset($this->options['users_id'])) {
            if (!$this->join_user = User::getByID($this->options['users_id'])) {
                throw new \InvalidArgumentException('Could not find that user', 400);
            }
        } else {
            $this->join_user = $this->current_user;
        }
        
        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to join this user', 403);
        }
        
        //Gather available and current roles
        $this->all_roles = new Roles\All();
        $this->membership = Member::getByUserIDAndSiteID($this->join_user->id, $this->site->id);
        
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
        return 'Join ' . $this->join_user->getName() . ' to ' . $this->site->base_url;
    }

    /**
     * Determine if the current_user can join the join_user
     * 
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->current_user) {
            //no current user set
            return false;
        }
        
        if (!$this->join_user) {
            //No join user set
            return false;
        }
        
        if ($this->current_user->isAdmin()) {
            //admin can join anyone
            return true;
        }
        
        if ($this->current_user->id == $this->join_user->id) {
            //The current user can join their self
            return true;
        }
        
        if (!$membership = Member::getByUserIDAndSiteID($this->current_user->id, $this->site->id)) {
            //The current user needs a membership if they want to join someone else.
            return false;
        }
        
        if ($membership->isVerified()) {
            //The current user needs to be verified to join someone else
            return true;
        }
        
        //Default to false
        return false;
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
                $this->membership = Member::createMembership($this->join_user, $this->site);
            }

            $this->membership->addRoles($add_roles);
        }

        //Find and remove all 'unselected' roles
        if ($this->membership) {
            $this->membership->removeRoles($this->getRolesToRemove($role_ids));
        }
        
        //Reset $this->membership, because removing roles could have also removed the membership
        $this->membership = Member::getByUserIDAndSiteID($this->join_user->id, $this->site->id);
        
        //If we need to be verified, redirect them to that form
        if ($this->membership && !$this->membership->isVerified()) {
            Controller::redirect($this->site->getURL() . 'verify/');
        }
        
        //Otherwise, redirect them to the members page for this site
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
