<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\Events\User\Search;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\RequiredLoginException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\User\User;
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
     * @var bool|User
     */
    public $user = false;
    
    /**
     * @var bool|Member
     */
    public $membership = false;
    
    public $stage = 1;
    
    public  $results = array();


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
        return 'Add a Member';
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['stage'])) {
            throw new InvalidArgumentException('You must pass a stage', 400);
        }
        
        $this->stage = $post['stage'];

        $method = 'handlePostForStage' . (int)$this->stage;
        
        $this->$method($get, $post, $files);
        
        $this->stage++;
    }
    
    public function handlePostForStage1($get, $post, $files)
    {
        //get results
        if (!isset($post['term'])) {
            throw new InvalidArgumentException('a term must be passed', 400);
        }

        $event = PluginManager::getManager()->dispatchEvent(
            Search::EVENT_NAME,
            new Search($post['term'])
        );
        
        $this->results = $event->getResults();
    }

    public function handlePostForStage2($get, $post, $files)
    {
        //get results
        if (!isset($post['user'])) {
            throw new InvalidArgumentException('a user must be selected', 400);
        }

        $user_details = explode('?', $post['user']);
        
        if (count($user_details) !== 2) {
            throw new UnexpectedValueException('A provider and uid must be specified');
        }
        
        if (!$user = User::getByUIDAndProvider($user_details[0], $user_details[1])) {
            //Get user details
            $event = PluginManager::getManager()->dispatchEvent(
                Search::EVENT_NAME,
                new Search($user_details[1])
            );
            
            $fields = array();
            
            $results = $event->getResults();

            if (isset($results[$post['user']])) {
                $fields = $results[$post['user']];
            }
            
            if (!$user = User::createUser($user_details[1], $user_details[0], $fields)) {
                throw new RuntimeException('Unable to create that user', 500);
            }
        }
        
        Controller::redirect($this->site->getJoinURL() . $user->id . '/');
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
