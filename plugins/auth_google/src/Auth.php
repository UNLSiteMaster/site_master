<?php
namespace SiteMaster\Plugins\Auth_Google;

use Opauth\Opauth;
use \SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\User\Session;
use SiteMaster\Core\User\User;
use SiteMaster\Core\Util;
use \SiteMaster\Core\ViewableInterface;

class Auth implements ViewableInterface
{
    /**
     * @var Opauth
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
            $info = array();
            $info['first_name'] = $result->info['first_name'];
            $info['last_name'] = $result->info['last_name'];
            $info['email'] = $result->info['email'];
            
            $user = User::createUser($result->uid, $result->provider, $info);
        }
        
        Session::logIn($user);
    }

    /**
     * Get the opauth object for this authentication plugin
     * 
     * @return Opauth
     */
    public function getOpauth()
    {
        $plugin = PluginManager::getManager()->getPluginInfo('auth_google');

        
        $options = array(
            'path' => Util::getBaseURLPath() . 'auth/',
            'callback_url' => 'auth/google/'
        );
        
        $options += $plugin->getOptions();

        return new Opauth($options);
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