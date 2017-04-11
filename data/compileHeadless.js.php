<?php

/**
 * @var $this \SiteMaster\Core\Auditor\HeadlessRunner
 */
$headless_file = $this->getCompiledScriptLocation();
    
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
    $details['options'] = $metric->options;
    
    $metrics[] = $details;
}

ob_start();
?>

var args = process.argv.slice(2);

var Nightmare = require('nightmare');
require('nightmare-inline-download')(Nightmare);
var options = {
    //We need to prevent download promts, which would otherwise cause the browser to wait for input
    ignoreDownloads: true
};
var browser = Nightmare(options);
var results = {};
var metrics = <?php echo json_encode($metrics) ?>;

browser.useragent('<?php echo SiteMaster\Core\Config::get('USER_AGENT') ?>/phantom');
browser.viewport(<?php echo SiteMaster\Core\Config::get('HEADLESS_WIDTH') ?>, <?php echo SiteMaster\Core\Config::get('HEADLESS_HEIGHT') ?>);

//Go to the page
var promise = browser.goto(args[0]);

promise.catch(function(error) {
    //This is likely an unrecoverable connection error...
    results.exception = error;
    console.log(JSON.stringify(results));
    return browser.end();
});

//Wait until we are ready
browser.wait(<?php echo (int) \SiteMaster\Core\Config::get('HEADLESS_WAIT') ?>);

//Define a metric handler to process the .then() part of promises
var metricHandler = function(result) {
    if (result && result.name) {
        //Record the result
        results[result.name] = result.results;
    }

    var metric = metrics.shift();
    if (metric) {
        //We have another metric to run
        var plugin = require(metric.file);

        //Pass an empty options array (reserved for future use)
        var newPromise = browser.use(plugin.evaluate(metric.options || []));

        //We will do the same thing as this promise, so handle it the same way! (yay recursion)
        newPromise.then(metricHandler);
        newPromise.catch(function(error) {
            //We shouldn't need to do anything here
            //Nothing will be put in the results for the metric, which the metric handler should account for
            //The name of the metric is also not available here, so tough luck I guess

            //We can still console.log the error to help with debugging later
            console.log(error);
        });

        return newPromise;
    } else {
        //We are done running metrics, return the results and exit
        console.log(JSON.stringify(results));
        return browser.end();
    }
};

//Now start our promise chain
promise.then(metricHandler);

promise.catch(function (error) {
    var result = {};
    result.exception = 'Unable to access network: ' + status;
    console.log(JSON.stringify(result));
});

<?php
$script = ob_get_contents();
ob_end_clean();
    
$result = file_put_contents($headless_file, $script);
