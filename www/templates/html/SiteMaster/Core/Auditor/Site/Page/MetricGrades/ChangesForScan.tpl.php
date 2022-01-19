<?php
$scan = $context->getScan();
$site = $scan->getSite();

?>
<section class="changes info-section">
    <header>
        <h3>Changes since the last scan</h3>
        <div class="subhead">
            These are metrics that have changed on your site.  The number of changes is the total number of marks for that metric compared to the last scan.  A negative value means that there was an improvement.
        </div>
    </header>

<?php
if ($context->count()) {
    ?>
    <table class="dcf-table" data-sortlist="[[0,0],[2,0]]">
        <thead>
        <tr>
            <th>Page</th>
            <th>Metric</th>
            <th>Number of Changes</th>
        </tr>
        </thead>
        <tbody>
        <?php

        foreach ($context as $metric_grade) {
            $page = $metric_grade->getPage();
            $metric = $metric_grade->getMetric();
            $metric_object = $metric->getMetricObject();
            ?>
            <tr>
                <td>
                    <a href="<?php echo $page->getURL()?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
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
                    <?php 
                    if ($metric_grade->changes_since_last_scan > 0) {
                        //add a plus sign to indicate more changes
                        echo '+'.$metric_grade->changes_since_last_scan . ' marks';
                    } else {
                        echo $metric_grade->changes_since_last_scan . ' marks';
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <?php
} else {
    ?>
    <p>
        No changes since the last scan
    </p>
    <?php
}
?>
</section>
