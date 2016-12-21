<?php

$runner = new \SiteMaster\Core\Auditor\HeadlessRunner();
$headless_file = $runner->getCompiledScriptLocation();
    
//Make an array of metrics to use
$metrics = [];
foreach (\SiteMaster\Core\Plugin\PluginManager::getManager()->getMetrics() as $metric) {
    /**
     * @var \SiteMaster\Core\Auditor\MetricInterface $metric
     */

    if (!$metric_headless_file = $metric->getHeadlessScript()) {
        //No headless script
        continue;
    }
    
    $details = [];
    
    //The root directory should be relative to the script's file
    $details['file'] = str_replace(\SiteMaster\Core\Util::getRootDir(), '..', $metric_headless_file);
    
    $metrics[] = $details;
}

ob_start();
?>

var args = process.argv.slice(2);

var Nightmare = require('nightmare');
var browser = Nightmare();
var results = {};
var metrics = <?php echo json_encode($metrics) ?>;

browser.useragent('<?php echo SiteMaster\Core\Config::get('USER_AGENT') ?>/phantom');
browser.viewport(<?php echo SiteMaster\Core\Config::get('HEADLESS_WIDTH') ?>, <?php echo SiteMaster\Core\Config::get('HEADLESS_HEIGHT') ?>);

//Go to the page
browser.goto(args[0]);

//Wait until we are ready
browser.wait(<?php echo (int)\SiteMaster\Core\Config::get('HEADLESS_WAIT') ?>);

//Define a metric handler to process the .then() part of promises
var metricHandler = function(result) {
    if (result.name) {
        //Record the result
        results[result.name] = result.results;
    }

    var metric = metrics.shift();
    if (metric) {
        //We have another metric to run
        var plugin = require(metric.file);

        //Pass an empty options array (reserved for future use)
        var newPromise = browser.use(plugin.evaluate([]));

        //We will do the same thing as this promise, so handle it the same way! (yay recursion)
        newPromise.then(metricHandler);
        return newPromise;
    } else {
        //We are done running metrics, return the results and exit
        console.log(JSON.stringify(results));
        return browser.end();
    }
};

var promise = browser.evaluate(function() {
    //This is just to get the chain rolling
    return true;
});

//Now start our promise chain
promise.then(metricHandler);

browser.catch(function (error) {
    var result = {};
    result.exception = 'Unable to access network: ' + status;
    console.log(JSON.stringify(result));
});

<?php
$script = ob_get_contents();
ob_end_clean();
    
$result = file_put_contents($headless_file, $script);
