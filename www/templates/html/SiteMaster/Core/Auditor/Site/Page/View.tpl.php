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
            Scanned URL: <?php echo $context->page->uri ?>
        </span>
    </div>
</header>
<?php
    if (!$context->page->isComplete()) {
        ?>
        <div class="panel notice">
            The scan has not finished for this page yet.  Refresh the page to get the most recent progress.
        </div>
        <?php
    }
?>

<div class="row">
    <div class="large-4 columns">
        <section class="in-page-nav info-section">
            <div>
                <header>
                    <h2>Scan Information</h2>
                </header>
                <ul>
                    <li><span class="scan-status">Status: <?php echo $context->page->status ?></span></li>
                    <li><span class="scanned-date">Scanned on: <?php echo $context->page->start_time ?></span></li>
                </ul>
            </div>
            <div>
                <header>
                    <h2>Links</h2>
                </header>
                <ul>
                    <li><a href="<?php echo $scan->getURL() ?>">Go back to the site scan</a></li>
                    <li><a href="<?php echo $context->page->uri;?>" target="_blank">View the scanned page</a></li>
                    <li><a href="<?php echo $context->getURL() . 'links-to-this/' ?>">View pages that link to this page</a></li>
                </ul>
                <?php echo $savvy->render($context->page->getScanForm()) ?>
            </div>
        </section>
    </div>

    <div class="large-8 columns">
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
    </div>
</div>
