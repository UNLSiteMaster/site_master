<?php
namespace SiteMaster\Core\User;

use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $user = false;

    /**
     * @param array $options
     * @throws \SiteMaster\Core\UnexpectedValueException
     * @throws \SiteMaster\Core\InvalidArgumentException
     */
    function __construct($options = array())
    {
        $this->options += $options;
        
        if (!isset($this->options['provider'], $this->options['uid'])) {
            throw new InvalidArgumentException('Both provider and uid must be provided', 400);
        }
        
        if (!$this->user = User::getByUIDAndProvider($this->options['uid'], $this->options['provider'])) {
            throw new UnexpectedValueException('That user could not be found', 400);
        }
    }

    /**
     * Get the url for this page
     * 
     * @return bool|string
     */
    public function getURL()
    {
        if (!$this->user) {
            return false;
        }
        
        return $this->user->getURL();
    }

    /**
     * Get the title for this page
     * 
     * @return string
     */
    public function getPageTitle()
    {
        if (!$this->user) {
            return "User Information";
        }
        
        return $this->user->getName();
    }
}
