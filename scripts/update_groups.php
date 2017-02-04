<?php
use SiteMaster\Core\Registry\GroupHelper;

ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$sites = new \SiteMaster\Core\Registry\Sites\All();

foreach ($sites as $site) {
    /**
     * @var $site \SiteMaster\Core\Registry\Site
     */
    if ($site->group_is_overridden == \SiteMaster\Core\Registry\Site::GROUP_IS_OVERRIDDEN_YES) {
        //Don't auto-change overridden groups
        continue;
    }
    
    $groupHelper = new GroupHelper();
    $group = $groupHelper->getPrimaryGroup($site->base_url);
    
    if ($group != $site->group_name) {
        $site->group_name = $group;
        $site->save();
    }
}
