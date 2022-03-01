<?php
ini_set('display_errors', true);

//Initialize all settings and auto loaders
require_once(__DIR__ . "/../init.php");

try {
    $scans = new \SiteMaster\Core\Auditor\Scans\All();
    $sitesProcessed = array();
    $orphanedScans= 0;
    foreach ($scans as $scan) {
        // skip sites already processed
        if (in_array($scan->sites_id, $sitesProcessed)) {
            continue;
        }
        try {
            $site = $scan->getSite();
            if (!empty($site)) {
                $site->cleanScans();
                $sitesProcessed[] = $site->id;
            } else {
                // orphaned scan (site no longer exists) so delete
                $scan->delete();
                ++$orphanedScans;
            }
        } catch (Exception $e) {
            echo 'Scan action failed with error: '. $e->getMessage() . "\n";
        }
    }
    echo 'Cleaned ' . count($sitesProcessed) . ' sites scans and deleted ' . $orphanedScans . " orphaned scans.\n";
} catch (Exception $e) {
    echo 'Process failed with error: '. $e->getMessage() . "\n";
}
