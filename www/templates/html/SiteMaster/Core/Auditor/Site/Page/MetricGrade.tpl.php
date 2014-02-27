<?php
$metric = $context->getMetric();
$metric_plugin = $metric->getMetricObject();
$page_marks = $context->getMarks();
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
    <div class="contents">
        <table>
            <thead>
                <tr>
                    <td>
                        Reason
                    </td>
                    <td>
                        Points Deducted
                    </td>
                    <td>
                        Location
                    </td>
                    <td>
                        Options
                    </td>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($page_marks as $page_mark) {
                    $mark = $page_mark->getMark();
                    ?>
                    <tr>
                        <td>
                            <?php echo $mark->name; ?>
                        </td>
                        <td>
                            <?php echo $page_mark->points_deducted; ?>
                        </td>
                        <td>
                            <?php
                            $location = 'Page';
                            if (!empty($page_mark->line) && !empty($page_mark->line)) {
                                $location = 'Line ' . $page_mark->line . ', Column ' . $page_mark->col;
                            }
                            if (!empty($page_mark->context)) {
                                $location .= ' Context: ' . $page_mark->context;
                            }
                            ?>
                            <?php echo $location; ?>
                        </td>
                        <td>
                            Fix
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

