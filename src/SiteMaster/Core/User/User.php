<?php

namespace SiteMaster\Core\User;

use DB\Record;
use SiteMaster\Core\Events\GetAuthenticationPlugins;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Sites\ApprovedForUser;

class User extends Record
{
    public $id;             //int required
    public $uid;            //varchar required
    public $provider;       //varchar required
    public $email;          //varchar
    public $first_name;     //varchar
    public $last_name;      //varchar
    public $role;           //enum('ADMIN', 'USER') default 'USER' required
    
    public function keys()
    {
        return array('id');
    }
    
    public static function getTable()
    {
        return 'users';
    }
    
    public static function createUser($uid, $provider, array $info = array())
    {
        $user = new self();
        $user->role = 'USER';
        $user->synchronizeWithArray($info);
        $user->uid = $uid;
        $user->provider = $provider;
        
        if (!$user->save()) {
            return false;
        }
        
        return $user;
    }

    /**
     * @param $uid
     * @param $provider
     * @return bool | \SiteMaster\User\User
     */
    public static function getByUIDAndProvider($uid, $provider)
    {
        return self::getByAnyField(__CLASS__, 'uid', $uid, 'provider = "' . \DB\RecordList::escapeString($provider) . '"');
    }

    /**
     * Get the authentication plugin for this user's provider
     * 
     * @return bool | \SiteMaster\Plugin\AuthenticationInterface object
     */
    public function getAuthenticationPlugin()
    {
        $authPlugins = PluginManager::getManager()->dispatchEvent(
            GetAuthenticationPlugins::EVENT_NAME,
            new GetAuthenticationPlugins()
        );

        foreach ($authPlugins->getPlugins() as $plugin) {
            if ($plugin->getProviderMachineName() == $this->provider) {

                return $plugin;
            }
        }
        
        return false;
    }

    /**
     * Get approved sites for this user
     * 
     * @return ApprovedForUser
     */
    public function getSites()
    {
        return new ApprovedForUser(array('user_id'=>$this->id));
    }


    /**
     * Get the full name of this user
     * 
     * @return string
     */
    public function getName()
    {
        if ($this->first_name || $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        
        return $this->uid;
    }
}