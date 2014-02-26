<?php
$scan = $context->getScan();
$site = $scan->getSite();
?>
<div class="changes">
    <h3>Changes since the last scan</h3>
    <table data-sortlist="[[0,0],[2,0]]">
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
        </tbody>
    </table>
</div>