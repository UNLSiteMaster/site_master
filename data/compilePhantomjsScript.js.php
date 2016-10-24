<?php

$runner = new \SiteMaster\Core\Auditor\PhantomjsRunner();
$phantom_js_file = $runner->getCompiledScriptLocation();

ob_start();
?>

/*global phantom */

var args = require('system').args;
var fs = require('fs');
var page = require('webpage').create();

page.settings.userAgent = '<?php echo SiteMaster\Core\Config::get('USER_AGENT') ?>/phantom';

page.viewportSize = {
    width: <?php echo SiteMaster\Core\Config::get('PHANTOMJS_WIDTH') ?>,
    height: <?php echo SiteMaster\Core\Config::get('PHANTOMJS_HEIGHT') ?>,
};

page.open(args[1], function (status) {
    // Check for page load success
    if (status !== 'success') {
        console.log('Unable to access network');
        return;
    }

    var results = {};
    var async_metrics = [];

    window.setTimeout(function () {
        // Change timeout as required to allow sufficient time
        
        <?php
        $metrics = \SiteMaster\Core\Plugin\PluginManager::getManager()->getMetrics();
        foreach ($metrics as $metric) {
            /**
             * @var \SiteMaster\Core\Auditor\MetricInterface $metric
             */
        
            $metric_phantom_js_file = $metric->getPhantomjsScript();
        
            if (file_exists($metric_phantom_js_file)) {
                ?>
                var metric_results = {};
                try {
                    <?php include $metric_phantom_js_file; ?>
                } catch (e) {
                    //There was an error...
                    metric_results.exception = e;
                }
                results.<?php echo $metric->getMachineName() ?> = metric_results;
                <?php
            }
        }
        ?>

        if (async_metrics.length) {
            page.onCallback = function (msg) {
                var index = async_metrics.indexOf(msg.metric);
    
                if (-1 == index) {
                    return;
                }
                
                //Remove the index from the array because we are no longer waiting on it
                async_metrics.splice(index, 1);
                
                if (typeof results[msg.metric] !== 'undefined') {
                    //Both sync and async were used. Merge em
                    for (var attr in msg.results) {
                        results[msg.metric][attr] = msg.results[attr];
                    }
                } else {
                    results[msg.metric] = msg.results;
                }
                
                if (async_metrics.length == 0) {
                    //We are no longer waiting on any metrics... exit
                    
                    console.log(JSON.stringify(results, null, '  '));
                    phantom.exit();
                }
            };
        } else {
            console.log(JSON.stringify(results, null, '  '));
    
            phantom.exit();
        }
    }, <?php echo (int)\SiteMaster\Core\Config::get('PHANTOMJS_WAIT') ?>);
});

<?php
$script = ob_get_contents();
ob_end_clean();
    
$result = file_put_contents($phantom_js_file, $script);
