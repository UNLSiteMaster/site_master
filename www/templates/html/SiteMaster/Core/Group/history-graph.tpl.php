<?php
use SiteMaster\Core\Config;

$data = array();
$data['dates'] = array();
$data['metric_history'] = array();
$last_data = array();
$previous_history = false;
$max_rows = 0;
$history_list = $context->getHistory(array('limit'=>360));

$i=0;
foreach ($history_list as $index=>$history) {
    if (count($history_list) > 21 && $previous_history) {
        $difference = strtotime($previous_history->date_created) - strtotime($history->date_created);
        $difference = floor($difference / (60 * 60 * 24)); //number of days
        if ($difference < 7) {
            //Only report one per week
            continue;
        }
    }
    
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
        if ($i > 0 && !isset($data['metric_history'][$metrics_id]['rows'])) {
            for ($ii = 0; $ii < $i; $ii++) {
                //Fill in missing data points, this is likely a new metric.
                $data['metric_history'][$metrics_id]['rows'][] = null;
            }
        }

        $data['metric_history'][$metrics_id]['rows'][] = $gpa;
        $max_rows = count($data['metric_history'][$metrics_id]['rows']);
    }

    $previous_history = $history;
    $i++;
}

foreach ($data['metric_history'] as $metrics_id=>$metrics_data) {
    //Make sure all metric rows are the same length (pad to the end with null)
    $data['metric_history'][$metrics_id]['rows'] = array_pad($data['metric_history'][$metrics_id]['rows'], $max_rows, null);
}

?>
<?php if (count($data['dates']) > 1): ?>
    <div class="graph-container">
        <h2>Group Metric History</h2>
        <p>The following is the group metric history over time. These numbers represent the percent of all pages in the group that pass the given metric. Note that only production sites are included in the calculation. Archived or in-development sites are excluded.</p>
        <canvas id="history_chart"></canvas>
        <div class="legend-container">
            <div id="history_legend"></div>
        </div>
        <div class="table">
            <button aria-expanded="false" class="button dcf-btn dcf-btn-secondary display-history-table">Show History Table</button>
            <table class="dcf-table" style="display:none">
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
    <?php
    $savvy->loadScriptDeclaration("
      $('.display-history-table').click(function() {
			var \$button = \$(this);
			var \$table = \$button.next('table');
			if (\$table.is(':hidden')) {
				\$table.show();
				\$button.text('Hide history table');
				\$button.attr('aria-expanded', 'true');
			} else {
				\$table.hide();
				\$button.text('Show history table');
				\$button.attr('aria-expanded', 'false');
			}
		});
		
		var data = {
			labels: " . json_encode(array_reverse($data['dates_long'])) .",
			datasets: [
				{
					label: \"Site GPA\",
					fillColor: \"#D00000\",
					strokeColor: \"#D00000\",
					pointColor: \"#D00000\",
					pointStrokeColor: \"#fff\",
					pointHighlightFill: \"#fff\",
					pointHighlightStroke: \"#D00000\",
					lineThickness: 5,
					data: " . json_encode(array_reverse($data['gpa'])) . "
				}
			]
		};
    " . renderHistoryJS($data['metric_history']));
    ?>

    <?php echo $savvy->render($context, 'SiteMaster/Core/Group/history-graph-exec.tpl.php'); ?>
<?php endif; ?>

<?php
function renderHistoryJS($history_data) {
    $content = "";
    $i = 1;
    foreach ($history_data as $metric_history) {

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

        $content .= 'data.datasets[' . $i . '] = {
                label: "' . $metric_history['title'] . '",
                fillColor: "' . $color . '",
                strokeColor: "' . $color . '",
                pointColor: "' . $color . '",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "' . $color . '",
                data: ' . json_encode(array_reverse($metric_history['rows'])) . '
            };
        ';
        $i++;
    }
    return $content;
}
?>
