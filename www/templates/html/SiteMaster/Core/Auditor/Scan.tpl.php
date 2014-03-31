<?php
$previous_scan = $context->getPreviousScan();
$site = $context->getSite();
$pages = $context->getPages();
$site_pass_fail = \SiteMaster\Core\Config::get('SITE_PASS_FAIL');
?>

<div class="scan">
    <header>
        <h2>Scan: <?php echo date("n-j-y g:i a", strtotime($context->start_time)); ?></h2>
        <div class="sub-info">
            Status: <?php echo $context->status;?>
            <?php
            if (!$context->isComplete()) {
                echo $savvy->render($context->getProgress());
            }
            ?>
        </div>
    </header>

    <?php
    if (!$context->isComplete()) {
        ?>
        <div class="panel notice">
            This scan has not finished yet.  This page will automatically refresh when the scan is complete.
        </div>
    <?php
    }
    ?>

    <?php
    if ($site_pass_fail && $context->isComplete()) {
        $passing = false;
        if ($context->gpa == 100) {
            $passing = true;
        }
        ?>
        <div class="dashboard-metrics">
            <div class="visual-island site-pass-fail-status <?php echo ($passing)?'valid':'invalid'; ?>">
                <span class="dashboard-value">
                    <?php
                    if ($passing) {
                        echo 'Looks Good';
                    } else {
                        echo 'Needs Work';
                    }
                    ?>
                </span>
                <span class="dashboard-metric">
                    <?php
                    if ($passing) {
                        echo 'All of your pages are passing.  Good job!';
                    } else {
                        echo 'In order for the site to pass, all pages must pass.';
                    }
                    ?>
                </span>
            </div>
        </div>
    <?php
    }
    ?>
    
    <section class="row dashboard-metrics">
        <div class="large-3 columns">
            <div class="visual-island gpa">
                <span class="dashboard-value"><?php echo $context->gpa ?><?php echo ($site_pass_fail?'%':'') ?></span>
                <?php
                $gpa_name = 'GPA';
                if ($site_pass_fail) {
                    $gpa_name = 'of pages are passing';
                }
                ?>
                <span class="dashboard-metric"><?php echo $gpa_name ?></span>
            </div>
        </div>
        <div class="large-3 columns">
            <div class="visual-island">
                <?php
                $arrow = "&#8596; <span class='secondary'>(same)</span>";
                if ($previous_scan) {
                    if ($previous_scan->gpa > $context->gpa) {
                        $arrow = "&#8595; <span class='secondary'>(worse)</span>";
                    } else if ($previous_scan->gpa < $context->gpa) {
                        $arrow = "&#8593; <span class='secondary'>(better)</span>";
                    }
                }
                ?>
                <div class="dashboard-value">
                    <?php echo $arrow ?>
                </div>
                <div class="dashboard-metric">Compared to Last Scan</div>
            </div>
        </div>
        <div class="large-3 columns">
            <div class="visual-island">
                <span class="dashboard-value"><?php echo $context->getABSNumberOfChanges() ?></span>
                <span class="dashboard-metric">Changes</span>
            </div>
        </div>
        <div class="large-3 columns">
            <div class="visual-island">
                <span class="dashboard-value"><?php echo $pages->count() ?></span>
                <span class="dashboard-metric">Pages</span>
            </div>
        </div>
    </section>

    <section>
        
        <?php 
        if ($previous_scan) {
            echo $savvy->render($context->getChangedMetricGrades());
        } else {
            //This is the first scan, don't the change list would probably be huge
            ?>
            <p class="change-list-first">
                Normally, a list of changes would be here.  However, this is the first time that we scanned your site.  In the future, you can see changes here.
            </p>
            <?php
        }
        ?>
        
        <div class="row">
            <div class="large-4 columns">
                <section class="hot-spots info-section">
                    <header>
                        <h3>Hot Spots</h3>
                        <div class="subhead">
                            These are areas on your site that need some love
                        </div>
                    </header>
                    <?php
                    foreach ($plugin_manager->getMetrics() as $metric) {
                        $metric_record = $metric->getMetricRecord();
                        $grades = $context->getHotSpots($metric_record->id);
                        ?>
                        <h4><?php echo $metric->getName()?></h4>
                        <?php echo $savvy->render($grades); ?>
                    <?php
                    }
                    ?>
                </section>
            </div>
            <div class="large-8 columns">
                <?php
                echo $savvy->render($pages);
                ?>
                
            </div>
        </div>
    </section>
</div>
