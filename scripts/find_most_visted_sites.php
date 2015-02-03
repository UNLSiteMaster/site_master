<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

if (!isset($argv[1])) {
    echo 'You must provide the path to a Google Analytics CSV export' . PHP_EOL;
    exit();
}

if (!file_exists($argv[1])) {
    echo "Unable to find mapping file" . PHP_EOL;
    exit();
}
$csv_file         = $argv[1];
$csv_contents     = file_get_contents($csv_file);
$csv_rows         = explode("\n", $csv_contents);

if (count($csv_rows) < 7) {
    echo "Invalid CSV export provided" . PHP_EOL;
    exit();
}

$registry                 = new \SiteMaster\Core\Registry\Registry();
$in_section               = 'head';
$processed_column_headers = false;
$totals                   = array();
foreach ($csv_rows as $row_number=>$row) {
    /**
     * [0] = url
     * [1] = pageviews
     */
    $data = str_getcsv($row, ",", '"');

    //Determine the section (separated by blank lines)
    if (empty($data[0])) {
        if ($in_section == 'head') {
            $in_section = 'body';
        } else {
            $in_section = 'footer';
        }
        continue;
    }
    
    //We only want to process the body of the csv
    if ($in_section != 'body') {
        continue;
    }
    
    //Skip column headers
    if (!$processed_column_headers) {
        $processed_column_headers = true;
        continue;
    }
    
    if ($data[0] == '(other)') {
        continue;
    }
    
    $url   = 'http://' . $data[0];
    $views = str_replace(',', '', $data[1]);

    if (!$closest_site = $registry->getClosestSite($url)) {
        echo 'Could not find a site for ' . $url . PHP_EOL;
        $root_url = str_replace('http%://', 'http://', $registry->getRootURL($url));

        if (false == $registry->URLIsAllowed($url)) {
            echo "\tNot a UNL Website" . PHP_EOL;
            continue;
        }
        
        echo "\tCreating $root_url" . PHP_EOL;
        
        $closest_site = \SiteMaster\Core\Registry\Site::createNewSite($root_url);
        continue;
    }

    if (!isset($totals[$closest_site->base_url])) {
        $totals[$closest_site->base_url] = 0;
    }
    
    $totals[$closest_site->base_url] += $views;
}

//Sort the array
arsort($totals);

//build csv
$csv = array();
$csv[] = array('Base URL', 'Total Views');

foreach ($totals as $base_url => $views) {
    $csv[] = array($base_url, $views);
}

$file = \SiteMaster\Core\Util::getRootDir() . '/tmp/top_views_sites.csv';

$fp = fopen($file, 'w');

foreach ($csv as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);
