<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Registry;
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
     * @var \SiteMaster\Core\Auditor\Scan
     */
    public $scan = false;

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $current_user = false;

    /**
     * The URI of the page to scan
     * 
     * @var bool
     */
    public $uri = false;

    /**
     * The previous page scan that this will be re-scanning.
     * 
     * @var bool|Page
     */
    public $page = false;


    function __construct($options = array())
    {
        $this->options += $options;

        if (!isset($this->options['uri'])) {
            throw new InvalidArgumentException('a page uri is required', 400);
        }
        
        $this->uri = urldecode($this->options['uri']);

        $registry = new Registry();
        if (!$this->site = $registry->getClosestSite($this->uri)) {
            throw new InvalidArgumentException('Could not site for that uri', 400);
        }
        
        if (!$this->scan = $this->site->getLatestScan()) {
            throw new InvalidArgumentException('There needs to be an existing site scan', 400);
        }
        
        //Get the previous page scan if we can.
        $this->page = Page::getByScanIDAndURI($this->scan->id, $this->uri);
        
        $this->current_user = Session::getCurrentUser();
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'scan/page/?uri=' . urlencode($this->uri);

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Page Site';
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
            throw new AccessDeniedException('You do not have permission to schedule a scan.  Please make sure that you are logged in and are a member of this site.', 403);
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
        if (!$this->page && $this->scan->getDistinctPageCount() >= Config::get('SCAN_PAGE_LIMIT')) {
            //Account for page limits
            Controller::redirect(
                $this->site->getURL(),
                new FlashBagMessage(FlashBagMessage::TYPE_ERROR, 'Can\'t schedule a page scan because the page limit has been reached')
            );
        }
        
        // Unless this is a new page, use the previous found with code
        $found_with = Page::FOUND_WITH_MANUAL;
        if ($this->page) {
            $found_with = $this->page->found_with;
        }
        
        $page_scan = Page::createNewPage($this->scan->id, $this->site->id, $this->uri, $found_with, array(
            'scan_type' => Page::SCAN_TYPE_USER,
        ));
        
        if ($page_scan->scheduleScan(Page::PRI_USER_SINGLE_PAGE_SCAN)) {
            Controller::redirect(
                $page_scan->getURL(),
                new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'A new scan for the has been scheduled.  This may take some time, but feel free to refresh the page to see any progress.')
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
