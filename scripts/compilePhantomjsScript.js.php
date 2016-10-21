<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$runner = new \SiteMaster\Core\Auditor\PhantomjsRunner();

$phantom_js_file = $runner->getCompiledScriptLocation();

ob_start();
?>

/*global phantom */

var args = require('system').args;
var fs = require('fs');
var page = require('webpage').create();

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
