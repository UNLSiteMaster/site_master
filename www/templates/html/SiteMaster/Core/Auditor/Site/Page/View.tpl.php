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
        <span class="page-url">
            <?php echo $context->page->uri ?>
            <a href="<?php echo $context->page->uri;?>" class="external" title="open the external page"><img src="<?php echo \SiteMaster\Core\Config::get('URL') ?>www/images/external.png" alt="link to external site"/></a>
        </span>
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
                $name = 'unknown';
                $metric_record = $metric_grade->getMetric();
                if ($metric_record && $metric_object = $metric_record->getMetricObject()) {
                    $name = $metric_object->getName();
                }
                ?>
                <tr>
                    <td><?php echo $name ?></td>
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
<div class="panel clear-fix">
    <div class="pull-right wdn-pull-right">
        <?php echo $savvy->render($context->page->getScanForm()) ?>
    </div>
</div>
