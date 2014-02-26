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
                <span class="dashboard-value date"><?php echo $context->getABSNumberOfChanges() ?></span>
                <span class="dashboard-metric">Changes</span>
            </div>
        </div>
        <div class="bp1-wdn-col-one-third">
            <div class="visual-island">
                <span class="dashboard-value date"><?php echo $pages->count() ?></span>
                <span class="dashboard-metric">Pages</span>
            </div>
        </div>
    </section>
    <section>
        <div class="changes">
            <h3>Changes since the last scan</h3>
            <table>
                <tr>
                    <th>Page</th>
                    <th>Metric</th>
                    <th>Number of Changes</th>
                </tr>
                <?php
                foreach ($context->getChangedMetricGrades() as $metric_grade) {
                    $page = $metric_grade->getPage();
                    $metric = $metric_grade->getMetric();
                    $metric_object = $metric->getMetricObject();
                    ?>
                    <tr>
                        <td>
                            <?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?>
                        </td>
                        <td>
                            <?php
                            $name = 'unknown';
                            if ($metric_object) {
                                $name = $metric_object->getName();
                            }
                            echo $name;
                            ?>
                        </td>
                        <td>
                            <?php echo $metric_grade->changes_since_last_scan ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <div class="wdn-grid-set">
            <div class="bp1-wdn-col-two-thirds">
                <div class="pages">
                    <h3>Pages</h3>
                    <table>
                        <tr>
                            <th>Path</th>
                            <th>Grade</th>
                        </tr>
                        <?php 
                        foreach ($pages as $page) {
                            ?>
                            <tr>
                                <td><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></td>
                                <td><?php echo $page->letter_grade ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div class="bp1-wdn-col-one-third">
                <div class="pages">
                    <h3>Hot Spots</h3>
                </div>
            </div>
        </div>
    </section>
</div>
