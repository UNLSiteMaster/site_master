<?php
$previous_scan = $context->getPreviousScan();
$site = $context->getSite();
$pages = $context->getPages();
?>

<div class="scan">
    <header>
        <h2>Scan: <?php echo date("n-j-y g:i a", strtotime($context->start_time)); ?></h2>
        <div class="sub-info">
            Status: <?php echo $context->status;?>
        </div>
    </header>
    <section class="wdn-grid-set dashboard-metrics">
        <div class="bp1-wdn-col-one-third">
                <div class="visual-island gpa">
                    <span class="dashboard-value"><?php echo $context->gpa ?></span>
                    <span class="dashboard-metric">GPA</span>
                </div>
        </div>
        <div class="bp1-wdn-col-one-third">
            <div class="visual-island">
                <span class="dashboard-value"><?php echo $context->getABSNumberOfChanges() ?></span>
                <span class="dashboard-metric">Changes</span>
            </div>
        </div>
        <div class="bp1-wdn-col-one-third">
            <div class="visual-island">
                <span class="dashboard-value"><?php echo $pages->count() ?></span>
                <span class="dashboard-metric">Pages</span>
            </div>
        </div>
    </section>
    <section>
        <?php echo $savvy->render($context->getChangedMetricGrades()); ?>
        
        <div class="wdn-grid-set">
            <div class="bp1-wdn-col-one-third">
                <div class="hot-spots">
                    <h3>Hot Spots</h3>
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
                </div>
            </div>
            <div class="bp1-wdn-col-two-thirds">
                <?php
                echo $savvy->render($pages);
                ?>
                
            </div>
        </div>
    </section>
</div>
