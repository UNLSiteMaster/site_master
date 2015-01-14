<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\AccessDeniedException;
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

class EditForm implements ViewableInterface, PostHandlerInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var Site
     */
    public $site = false;

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $current_user = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find that site', 400);
        }

        $this->current_user = Session::getCurrentUser();
        
        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to edit this site.  You must be a verified member.', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'edit/';

    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Edit Site Information';
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
        
        return false;
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['action'])) {
            throw new InvalidArgumentException('An action must be specified', 400);
        }
        
        switch ($post['action']) {
            case 'edit':
                $this->edit($get, $post, $files);
                break;
            case 'delete':
                $this->delete($get, $post, $files);
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
    protected function edit($get, $post, $files)
    {
        if (isset($post['title'])) {
            $this->site->title = $post['title'];
        }
        
        if (isset($post['support_email'])) {
            $this->site->support_email = $post['support_email'];
        }

        if (isset($post['support_groups'])) {
            $this->site->support_groups = $post['support_groups'];
        }
        
        if (isset($post['production_status']) && in_array($post['production_status'], array(
                Site::PRODUCTION_STATUS_PRODUCTION,
                Site::PRODUCTION_STATUS_DEVELOPMENT,
                Site::PRODUCTION_STATUS_ARCHIVED,
            ))) {
            $this->site->production_status = $post['production_status'];
        }
        
        $this->site->save();
        
        Controller::redirect(
            $this->site->getURL(),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Site updated')
        );
    }

    /**
     * handle the delete post action
     *
     * @param $get
     * @param $post
     * @param $files
     * @throws \SiteMaster\Core\RuntimeException
     */
    protected function delete($get, $post, $files)
    {
        if (!$this->site->delete()) {
            throw new RuntimeException('Unable to delete the site', 400);
        }
        
        Controller::redirect(
            Config::get('URL'),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Site deleted')
        );
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
