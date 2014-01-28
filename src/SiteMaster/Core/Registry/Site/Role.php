<?php
namespace SiteMaster\Core\Registry\Site;

use DB\Record;

class Role extends Record
{
    public $id;               //int required
    public $role_name;        //varchar required
    public $description;      //longtext
    public $protected;        //enum('YES', NO') default=no

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'roles';
    }

    /**
     * Get a role object by a role name
     * 
     * @param $role_name
     * @return false|Role
     */
    public static function getByRoleName($role_name)
    {
        return self::getByAnyField(__CLASS__, 'role_name', $role_name);
    }

    /**
     * Create a new role
     *
     * @param $role_name
     * @param array $options
     * @return bool|Role
     */
    public static function createRole($role_name, $options = array())
    {
        $role = new self();
        $role->protected = 'NO';
        $role->synchronizeWithArray($options);
        $role->role_name = $role_name;
        
        if (!$role->insert()) {
            return false;
        }
        
        return $role;
    }
}
