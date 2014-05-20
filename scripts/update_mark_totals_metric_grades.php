<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//Update all pages to populate the num_errors and num_notices
$pages = new \SiteMaster\Core\Auditor\Site\Pages\All();
foreach ($pages as $page) {
    /**
     * @var $page \SiteMaster\Core\Auditor\Site\Page
     */
    if (!$page->isComplete()) {
        //We only want to update pages that have finished scanning
        continue;
    }

    $errors  = $page->getErrors();
    $notices = $page->getNotices();

    $page->num_errors  = $errors->count();
    $page->num_notices = $notices->count();

    $page->save();
}