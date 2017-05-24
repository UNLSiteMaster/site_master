<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Override;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Auditor\Site\Page\Mark;
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

class AddOverrideForm implements ViewableInterface, PostHandlerInterface
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
     * @var Mark
     */
    public $page_mark;

    /**
     * @var \SiteMaster\Core\Auditor\Metric\Mark
     */
    public $mark;

    /**
     * @var Page
     */
    public $page;


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
        
        if (!isset($options['page_mark'])) {
            throw new InvalidArgumentException('A page mark ID must be passed', 400);
        }

        if (!$this->page_mark = Mark::getById($this->options['page_mark'])) {
            throw new InvalidArgumentException('Could not find that page_mark', 400);
        }
        
        if ($this->page_mark->points_deducted !== '0.00') {
            throw new InvalidArgumentException('Sorry, only notices can be overridden.', 400);
        }

        if (!$this->page = $this->page_mark->getPage()) {
            throw new InvalidArgumentException('Could not find that page record', 400);
        }

        if (!$this->mark = $this->page_mark->getMark()) {
            throw new InvalidArgumentException('Could not find that mark record', 400);
        }

        $this->current_user = Session::getCurrentUser();

        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to add an override', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'overrides/add/';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Add an override';
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
        if (!isset($post['scope'])) {
            throw new InvalidArgumentException('a scope must be specified', 400);
        }

        if (!isset($post['reason']) || empty($post['reason'])) {
            throw new InvalidArgumentException('a reason must be specified', 400);
        }

        $override = Override::createNewOverride($post['scope'], $this->current_user->id, $post['reason'], $this->page_mark);
        
        if ($override) {
            Controller::redirect(
                $this->site->getURL().'overrides/',
                new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Override created')
            );
        } else {
            Controller::redirect(
                $this->site->getURL().'overrides/',
                new FlashBagMessage(FlashBagMessage::TYPE_ERROR, 'Sorry, there was an error creating the override.')
            );
        }
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
