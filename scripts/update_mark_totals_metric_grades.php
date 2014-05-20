<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//Update all pages to populate the num_errors and num_notices
$grades = new \SiteMaster\Core\Auditor\Site\Page\MetricGrades\All();
foreach ($grades as $grade) {
    /**
     * @var $grade \SiteMaster\Core\Auditor\Site\Page\MetricGrade
     */
    $errors  = $grade->getErrors();
    $notices = $grade->getNotices();

    $grade->num_errors  = $errors->count();
    $grade->num_notices = $notices->count();

    $grade->save();
}