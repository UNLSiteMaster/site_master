<?php
use SiteMaster\Core\Auditor\Group\History\GroupHistory;
use SiteMaster\Core\Auditor\Group\History\MetricHistory;
use SiteMaster\Core\Config;

ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$grade_helper = new \SiteMaster\Core\Auditor\GradingHelper();

foreach (Config::get('GROUPS') as $group_name=>$group) {
    $sites = new \SiteMaster\Core\Registry\Sites\WithGroup(['group_name' => $group_name]);
    
    $site_gpa = [];
    $total_pages = 0;
    $metric_gpa = [];
    
    foreach ($sites as $site) {
        /**
         * @var $site \SiteMaster\Core\Registry\Site
         */
        
        if ($site->production_status == \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_ARCHIVED) {
            //exclude archived
            continue;
        }

        if ($site->production_status == \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_DEVELOPMENT) {
            //exclude sites that are in development
            continue;
        }
        
        if (!$scan = $site->getLatestScan(true)) {
            continue;
        }
        
        $history = $site->getHistory();
        
        if (0 === count($history)) {
            continue;
        }

        $history->seek(count($history)-1);
        $latest = $history->current();

        /**
         * @var $latest \SiteMaster\Core\Auditor\Site\History\SiteHistory
         */
        $site_gpa[] = $latest->gpa;
        $total_pages += $latest->total_pages;
        
        foreach ($latest->getMetricHistory() as $metric_history) {
            /**
             * @var $metric_history \SiteMaster\Core\Auditor\Site\History\MetricHistory
             */
            $record = $metric_history->getMetric();
            if (!$record->getMetricObject()) {
                //Don't include removed metrics
                continue;
            }
            
            if (!isset($metric_gpa[$metric_history->metrics_id])) {
                $metric_gpa[$metric_history->metrics_id] = [];
            }
            $metric_gpa[$metric_history->metrics_id][] = $metric_history->gpa;
        }
    }
    
    $group_record = GroupHistory::createNewGroupHistory($group_name, $grade_helper->averageGPAs($site_gpa), $total_pages);

    foreach ($metric_gpa as $metrics_id => $grades) {
        $metric_record = MetricHistory::createNewMetricHistory($group_record->id, $metrics_id, $grade_helper->averageGPAs($grades));
    }
}
