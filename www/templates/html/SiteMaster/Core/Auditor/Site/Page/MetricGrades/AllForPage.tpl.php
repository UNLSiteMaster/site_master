<?php
foreach ($context as $grade) {
    if (!$metric_record = $grade->getMetric()) {
        echo "<p>unknown metric</p>";
        continue;
    }
    
    if (!$metric_record->getMetricObject()) {
        echo "<p>unknown metric</p>";
        continue;
    }
    echo $savvy->render($grade);
}
