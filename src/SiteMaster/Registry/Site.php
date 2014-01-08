<?php
namespace SiteMaster\Registry;

use DB\Record;

class Site extends Record
{
    public $id;               //int required
    public $base_url;         //varchar required
    public $title;            //varchar
    public $support_email;    //varchar

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'sites';
    }
    
    public function getMembers()
    {
        
    }
}
