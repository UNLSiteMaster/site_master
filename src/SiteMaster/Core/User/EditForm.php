<?php
namespace SiteMaster\Core\User;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class EditForm implements ViewableInterface, PostHandlerInterface
{
    /**
     * @var array
     */
    public $options = array();
    
    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $current_user = false;

    function __construct($options = array())
    {
        $this->options += $options;

        //get the site
        $this->current_user = Session::getCurrentUser();
        
        if (!$this->current_user) {
            throw new AccessDeniedException('You need to be logged in to access this', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return Config::get('URL') . 'user/edit/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'My Information and Settings';
    }

    public function handlePost($get, $post, $files)
    {

        if (isset($post['is_private']) && $post['is_private'] === '1') {
            $this->current_user->is_private = User::PRIVATE_YES;
        } else {
            $this->current_user->is_private = User::PRIVATE_NO;
        }
        
        $this->current_user->save();

        Controller::redirect(
            $this->getURL(),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Settings have been saved')
        );
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
