<?php
$scan = $context->page->getScan();
$metric_grades = $context->page->getMetricGrades();
?>
<header class="page-scan-header">
    <div class="letter-grade-container">
        <span class="letter-grade unknown"><?php echo $context->page->letter_grade?></span>
    </div>
    <div class="details">
        <span class="title">Page: <?php echo $context->page->getTitle(); ?></span>
        <span class="page-url"><?php echo $context->page->uri ?></span>
        <div class="scan-info">
            <span class="scanned-date">Scanned on: <?php echo $context->page->start_time ?></span>
            <a href="<?php echo $scan->getURL() ?>">Go back to the site scan</a>
        </div>
        
    </div>
</header>
<div class="page-scan-content">
    <?php
    echo $savvy->render($metric_grades);
    ?>
</div>
<div class="page-scan-scoring">
    Scoring
    <table>
        <thead>
        <tr>
            <th>Metric</th>
            <th>Weighted Score</th>
        </tr>
        </thead>
        <tbody>
            <?php
            foreach ($metric_grades as $metric_grade) {
                $metric_record = $metric_grade->getMetric();
                $metric_object = $metric_record->getMetricObject();
                ?>
                <tr>
                    <td><?php echo $metric_object->getName() ?></td>
                    <td><?php echo $metric_grade->weighted_grade ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    Total:
                </td>
                <td class="total">
                    <?php echo $context->page->point_grade ?>/<?php echo $context->page->points_available ?> = <?php echo $context->page->letter_grade ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
