<?php
namespace SiteMaster\Plugins\Auth_Google;

use \SiteMaster\Config;
use SiteMaster\Session;
use SiteMaster\User\User;
use SiteMaster\Util;
use \SiteMaster\ViewableInterface;

class Auth implements ViewableInterface
{
    /**
     * @var \Opauth\Opauth
     */
    protected $opauth;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param array $options
     */
    function __construct($options = array())
    {
        $this->opauth = $this->getOpauth();
        $this->options += $options;
        
        if (strpos($options['current_url'], 'callback') !== false) {
            //handle callback
            $this->handleCallback();
        } else {
            //Authenticate
            $this->authenticate();
        }
    }

    /**
     * Authenticate the user
     */
    public function authenticate()
    {
        $this->opauth->run();
    }

    /**
     * Handle a callback from opauth
     * 
     * This should also create an account for the user and log them in.
     */
    public function handleCallback()
    {
        if (!$result = $this->opauth->run()) {
            throw new Exception("Oops.  It looks like you failed to log in with google.  :(", 400);
        }
        
        if (!$user = User::getByUIDAndProvider($result->uid, $result->provider)) {
            $user = User::createUser($result->uid, $result->provider, $result->info['email'], $result->info['first_name'], $result->info['last_name']);
        }
        
        \SiteMaster\User\Session::logIn($user);
    }

    /**
     * Get the opauth object for this authentication plugin
     * 
     * @return \Opauth\Opauth
     */
    public function getOpauth()
    {
        $plugin = \SiteMaster\Plugin\PluginManager::getManager()->getPluginInfo('auth_google');

        
        $options = array(
            'path' => Util::getBaseURLPath() . 'auth/',
            'callback_url' => 'auth/google/'
        );
        
        $options += $plugin->getOptions();

        return new \Opauth\Opauth($options);
    }

    /**
     * The URL for this page
     * 
     * @return string
     */
    public function getURL()
    {
        return Config::get('URL') . 'auth/google/';
    }

    /**
     * The page title for this page
     * 
     * @return string
     */
    public function getPageTitle()
    {
        return "Google Auth";
    }
}