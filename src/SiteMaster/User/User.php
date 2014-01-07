<?php

namespace SiteMaster\User;

use DB\Record;
use DB\RecordList;

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
    
    public static function createUser($uid, $provider, $email, $first_name = '', $last_name = '', $role = 'USER')
    {
        $user = new self();
        $user->uid = $uid;
        $user->email = $email;
        $user->provider = $provider;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->role = $role;
        
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
}