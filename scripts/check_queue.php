<?php
/**
 * It is recommenced to run this on a CRON at least once every 15min.  If email logs are configured, it should email you.
 */

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//Get the queue
$queue = new SiteMaster\Core\Auditor\Site\Pages\Queued();

if (0 == $queue->count()) {
    exit(); //Nothing is in the queue, everything is okay.
}

//Get the last scanned page
$finished_pages = new SiteMaster\Core\Auditor\Site\Pages\Finished();

if (0 == $finished_pages->count()) {
    exit(); //Nothing is finished, so there is nothing to compare times to... end early.
}

$finished_pages->rewind();
$latest_page = $finished_pages->current();

if (strtotime($latest_page->end_time) < strtotime('10 minutes ago')) {
    SiteMaster\Core\Util::log(Monolog\Logger::CRITICAL, 'The queue appears to be stalled.  Pages are in queue but not being scanned in a timely fashion.');
}

