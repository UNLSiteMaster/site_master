<?php

namespace SiteMaster\Core\User;

use DB\Record;
use SiteMaster\Core\Config;
use SiteMaster\Core\Events\GetAuthenticationPlugins;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Sites\ApprovedForUser;
use SiteMaster\Core\Registry\Sites\PendingForUser;

class User extends Record
{
    public $id;             //int required
    public $uid;            //varchar required
    public $provider;       //varchar required
    public $email;          //varchar
    public $first_name;     //varchar
    public $last_name;      //varchar
    public $role;           //enum('ADMIN', 'USER') default 'USER' required
    public $last_login;     //datetime()
    public $total_logins;   //INT
    public $is_private; //enum('YES', 'NO') NOT NULL DEFAULT 'YES'
    
    const PRIVATE_NO = 'NO';
    const PRIVATE_YES = 'YES';
    
    public function keys()
    {
        return array('id');
    }
    
    public function insert()
    {
        if (null === $this->total_logins) {
            $this->total_logins = 0;
        }
        
        return parent::insert();
    }
    
    public function update()
    {
        if (null === $this->total_logins) {
            $this->total_logins = 0;
        }
        
        return parent::update();
    }
    
    public static function getTable()
    {
        return 'users';
    }
    
    public static function createUser($uid, $provider, array $info = array())
    {
        $user = new self();
        $user->role = 'USER';
        $user->is_private = self::PRIVATE_YES;
        $user->synchronizeWithArray($info);
        $user->uid = $uid;
        $user->provider = $provider;
        $user->last_login = null;
        $user->total_logins = 0;
        
        if (!$user->save()) {
            return false;
        }
        
        return $user;
    }

    /**
     * @param $uid
     * @param $provider
     * @return bool | \SiteMaster\Core\User\User
     */
    public static function getByUIDAndProvider($uid, $provider)
    {
        return self::getByAnyField(__CLASS__, 'uid', $uid, 'provider = "' . \DB\RecordList::escapeString($provider) . '"');
    }

    /**
     * Get approved sites for this user
     * 
     * @return ApprovedForUser
     */
    public function getApprovedSites()
    {
        return new ApprovedForUser(array('user_id'=>$this->id));
    }

    /**
     * Get approved sites for this user
     *
     * @return ApprovedForUser
     */
    public function getPendingSites()
    {
        return new PendingForUser(array('user_id'=>$this->id));
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

    /**
     * Determine if this user is an admin
     * 
     * @return bool
     */
    public function isAdmin()
    {
        if ($this->role != 'ADMIN') {
            return false;
        }
        
        return true;
    }

    /**
     * Get the view URL for this user
     * 
     * @return string
     */
    public function getURL()
    {
        return Config::get('URL') . 'users/' . $this->provider . '/' . $this->uid . '/';
    }
}