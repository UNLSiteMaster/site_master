<?php
use SiteMaster\Core\Config;

$data = array();
$data['dates'] = array();
$data['metric_history'] = array();
$last_data = array();

foreach ($context->site->getHistory(array('limit'=>100)) as $index=>$history) {
    $date = date('Y-m-d', strtotime($history->date_created));
    
    $new_data = array(
        'date' => $date,
        'total_pages' => $history->total_pages,
        'gpa' => $history->gpa
    );

    foreach ($history->getMetricHistory() as $metric_history) {
        if (!isset($data['metric_history'][$metric_history->metrics_id])) {
            $metric_object = $metric_history->getMetric()->getMetricObject();

            $metric_name = 'unknown';
            if ($metric_object) {
                $metric_name = $metric_object->getName();
            }

            $data['metric_history'][$metric_history->metrics_id] = array(
                'title' => $metric_name
            );
        }

        $new_data['metric_history'][$metric_history->metrics_id] = $metric_history->gpa;
    }
    
    if ($new_data == $last_data) {
        //Remove duplicates (no change in data) as long as they are on the same day
        //This will hopefully help to un-clutter the graph
        continue;
    }
    
    $last_data = $new_data;

    //Add data to the graph array
    $data['dates'][]       = $date;
    $data['dates_long'][]  = $history->date_created;
    $data['total_pages'][] = $history->total_pages;
    $data['gpa'][]         = $history->gpa;
 
    foreach ($new_data['metric_history'] as $metrics_id=>$gpa) {
        $data['metric_history'][$metrics_id]['rows'][] = $gpa;
    }
}
?>
<?php if (count($data['dates']) > 1): ?>
    <div class="graph-container">
        <h2>Site History</h2>
        <canvas id="history_chart"></canvas>
        <div class="legend-container">
            <div id="history_legend"></div>
        </div>
        <div class="table">
            <table class="wdn-text-hidden show-for-sr">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Site GPA</th>
                    <?php
                    foreach ($data['metric_history'] as $metric) {
                        echo '<th>' . $metric['title'] . '</th>';
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['dates'] as $key=>$details): ?>
                    <tr>
                        <td><?php echo $data['dates_long'][$key] ?></td>
                        <td><?php echo (isset($data['gpa'][$key]))?$data['gpa'][$key]:'' ?></td>
                        <?php foreach ($data['metric_history'] as $metric): ?>
                            <td><?php echo (isset($metric['rows'][$key]))?$metric['rows'][$key]:'' ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        var data = {
            labels: <?php echo json_encode($data['dates_long']) ?>,
            datasets: [
                {
                    label: "Site GPA",
                    fillColor: "#D00000",
                    strokeColor: "#D00000",
                    pointColor: "#D00000",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "#D00000",
                    lineThickness: 5,
                    data: <?php echo json_encode($data['gpa']) ?>
                }
            ]
        };

        <?php 
        $i = 1;
        foreach ($data['metric_history'] as $metric_history) {
        
            switch ($i) {
                case 1:
                    $color = '#34A7FF';
                    break;
                case 2:
                    $color = '#36C700';
                    break;
                case 3:
                    $color = '#595959';
                    break;
                case 4:
                    $color = '#1b6300';
                    break;
                case 5:
                    $color = '#005596';
                    break;
                case 6:
                    $color = '#496D89';
                    break;
                case 7:
                    $color = '#9CD4FF';
                    break;
                default:
                    $color = '#e9B800';
            }
            ?>
            data.datasets[<?php echo $i?>] = {
                label: "<?php echo $metric_history['title'] ?>",
                fillColor: "<?php echo $color ?>",
                strokeColor: "<?php echo $color ?>",
                pointColor: "<?php echo $color ?>",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "<?php echo $color ?>",
                data: <?php echo json_encode($metric_history['rows']) ?>
            }
            <?php
            $i++;
        }
        ?>
    </script>

    <?php echo $savvy->render($context, 'SiteMaster/Core/Registry/Site/history-graph-exec.tpl.php'); ?>
<?php endif; ?>