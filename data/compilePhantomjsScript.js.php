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

    console.log(JSON.stringify(results, null, '  '));

    phantom.exit();
});

<?php
$script = ob_get_contents();
ob_end_clean();
    
$result = file_put_contents($phantom_js_file, $script);
