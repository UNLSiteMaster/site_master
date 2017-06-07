<?php
$metric = $context->getMetric();
$metric_plugin = $metric->getMetricObject();
$page_marks = $context->getMarks();
$passing_marks = $context->getPasses();
$page = $context->getPage();
?>
<div class="metric-grade-details grade_<?php echo strtolower($context->letter_grade) ?>" id="metric_<?php echo $metric->id ?>">
    <header class="header">
        <div class="details">
            <h3 class="title"><?php echo $metric_plugin->getName(); ?></h3>
            <?php
            $changes = 0;
            if (!empty($context->changes_since_last_scan)) {
                $changes = $context->changes_since_last_scan;
            }
            $message = '';
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
            <?php
            if (!$context->isPassFail()) {
                ?>
                <span class="earned"><?php echo $context->point_grade?><span class="points_available">/<?php echo $context->points_available?></span></span>
                <?php
            }
            ?>
            <?php if ($context->weight === '0.00'): ?>
                <span class="weight">Does not affect the page grade</span>
            <?php else: ?>
                <span class="weight"><?php echo $context->weight ?> points of total score</span>
            <?php endif; ?>
        </div>
        <div class="letter-grade-container">
            <span class="letter-grade unknown"><?php echo $context->letter_grade?></span>
        </div>
    </header>
    <?php 
    try {
        $description = $savvy->render($metric_plugin);
    } catch (\Savvy_TemplateException $e) {
        $description = false;
    }
    
    if ($description) {
        ?>
        <div class="metric-description">
            <?php echo $description ?>
        </div>
        <?php
    }
    ?>
    
    <div class="contents">
    <?php
    if ($page_marks->count()) {
        ?>
        <table>
            <thead>
            <tr>
                <th>
                    Reason
                </th>
                <th>
                    <?php
                    $title = 'Points Deducted';
                    if ($context->isPassFail()) {
                        $title = 'Pass/Fail';
                    }
                    echo $title;
                    ?>
                    
                </th>
                <th>
                    Options
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($page_marks as $page_mark) {
                $mark = $page_mark->getMark();
                ?>
                <tr>
                    <td>
                        <span class="<?php echo $mark->machine_name ?>"><?php echo $mark->name; ?></span>
                        <?php if (strlen($page_mark->value_found) <= 256): ?>
                            <span class="value-found-sm"><?php echo $metric_plugin->formatValueFound($mark->machine_name, $page_mark->value_found) ?></span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php
                        $points_deducted = $page_mark->points_deducted;
                        if ($context->isPassFail()) {
                            if ($page_mark->points_deducted) {
                                $points_deducted = 'Fail';
                            } else {
                                $points_deducted = 'Pass';
                            }
                        }
                        if ($page_mark->points_deducted === '0.00') {
                            if ($context->isPassFail()) {
                                $points_deducted = 'notice';
                            } else {
                                $points_deducted = '0 (notice)';
                            }
                        }
                        echo $points_deducted;
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo $page->getURL() . 'marks/' . $page_mark->id ?>/" target="_blank">Fix</a>
                    </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
        
        <?php
    } else if ($context->letter_grade == \SiteMaster\Core\Auditor\GradingHelper::GRADE_INCOMPLETE) {
        ?>
        <p>
            We were unable to scan this page.  This might be because of an error with our scanner, or it might be because of an error on the page.  Please make sure that the page passes HTML validation and there are no JavaScript errors.
        </p>
        <?php
    } else {
        ?>
        <p>Everything looks good!  Keep up the good work!</p>
        <?php
    }
    ?>

    <?php if ($passing_marks->count()): ?>
        <h4>Passing</h4>
        <ul>
            <?php foreach($passing_marks as $page_mark): ?>
                <?php $mark = $page_mark->getMark(); ?>
                <li><?php echo $mark->name ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
        
    </div>
</div>

