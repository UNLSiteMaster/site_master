<?php
$metric = $context->getMetric();
$metric_plugin = $metric->getMetricObject();
?>
<div class="metric-grade-details">
    <header class="header">
        <span class="letter-grade-container">
            <span class="letter-grade unknown"><?php echo $context->letter_grade?></span>
        </span>
        <div class="details">
            <span class="title"><?php echo $metric_plugin->getName(); ?></span>
            <?php
            $changes = 0;
            if (!empty($context->changes_since_last_scan)) {
                $changes = $context->changes_since_last_scan;
            }
            $message = 'Everything looks good!';
            $class = 'same-marks';
            
            if ($changes > 0) {
                $message = 'What happened?!';
                $class = 'more-marks';
            }

            if ($changes < 0) {
                $message = 'Keep it up!';
                $class = 'less-marks';
            }

            ?>
            <span class="changes <?php echo $class?>"><?php echo $changes ?> changes since the last scan.  <?php echo $message ?></span>
        </div>
        <div class="score">
            <span class="earned"><?php echo $context->point_grade?><span class="points_available">/<?php echo $context->points_available?></span></span>
            <span class="weight"><?php echo $context->weight?> points of total score</span>
        </div>
    </header>
</div>

