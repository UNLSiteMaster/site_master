<?php
use SiteMaster\Core\Config;

$data = array();
$data['metric_history'] = array();
foreach ($context->site->getHistory(array('limit'=>100)) as $index=>$history) {
    $data['dates'][] = $history->date_created;
    $data['total_pages'][] = $history->total_pages;
    $data['gpa'][] = $history->gpa;

    foreach ($history->getMetricHistory() as $metric_history) {
        if (!isset($data['metric_history'][$metric_history->metrics_id])) {
            $data['metric_history'][$metric_history->metrics_id] = array(
                'title' => $metric_history->getMetric()->getMetricObject()->getName(),
                'rows' => array()
            );
        }

        $data['metric_history'][$metric_history->metrics_id]['rows'][] = $metric_history->gpa;
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
    </div>
    <script>
        var data = {
            labels: <?php echo json_encode($data['dates']) ?>,
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
                },
                {
                    label: "Total Pages",
                    fillColor: "rgba(151,187,205,0.2)",
                    strokeColor: "rgba(151,187,205,1)",
                    pointColor: "rgba(151,187,205,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(151,187,205,1)",
                    data: <?php echo json_encode($data['total_pages']) ?>
                }
            ]
        };

        <?php 
        $i = 2;
        foreach ($data['metric_history'] as $metric_history) {
        
            switch ($i) {
                case 2:
                    $color = '#34A7FF';
                    break;
                case 3:
                    $color = '#36C700';
                    break;
                case 4:
                    $color = '#595959';
                    break;
                case 5:
                    $color = '#94C160';
                    break;
                case 6:
                    $color = '#005596';
                    break;
                case 7:
                    $color = '#496D89';
                    break;
                case 8:
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