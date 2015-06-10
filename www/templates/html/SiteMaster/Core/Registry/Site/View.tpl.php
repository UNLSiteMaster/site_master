<?php
use SiteMaster\Core\Config;

if ($user && $membership = $context->site->getMembershipForUser($user->getRawObject())) {
    $display_notice = false;
    $verified = $membership->isVerified();
    $unapproved = false;
    
    $roles = $membership->getRoles();
    foreach ($roles as $role) {
        if (!$role->isApproved()) {
            $display_notice = true;
            $unapproved = true;
        }
    }
    
    if (!$verified || $unapproved) {
        ?>
        <div class="notice">
            <h2>
                It looks like you are unverified or have unapproved roles.
            </h2>
            <p>
                <?php
                if (!$verified) {
                    ?>
                    <a href="<?php echo $context->site->getURL() . 'verify/' ?>" class="button wdn-button">Verify Me Now</a>
                <?php
                }
                ?>
                <?php
                if ($unapproved) {
                    ?>
                    <a href="<?php echo $context->site->getURL() . 'join/' ?>" class="button wdn-button">Edit My Roles</a>
                <?php
                }
                ?>
            </p>
        </div>
        <?php
    }
}

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
    <script src="<?php echo Config::get('URL') . 'www/js/vendor/chart.min.js' ?>"></script>
    <div class="graph-container">
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
    
        var ctx = document.getElementById("history_chart").getContext("2d");
        var chart = new Chart(ctx).Line(data, {
            responsive: false,
            maintainAspectRatio: false,
            datasetFill: false,
            legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span class=\"color\" style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
            tooltipFontSize: 10,
            tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
            multiTooltipTemplate: "<%if (datasetLabel){%><%=datasetLabel%>: <%}%><%= value %>",
        });
        
        //console.log(chart.generateLegend());
        WDN.jQuery("#history_legend").html(chart.generateLegend());
    </script>
<?php endif; ?>
<div class="scan-include">
    <?php
    if ($scan = $context->getScan()) {
        ?>
        <script type="text/javascript">
            var request = $.ajax("<?php echo $scan->getURL() ?>?format=partial");
            request.done(function(html) {
                $("#scan_ajax").html(html);
                sitemaster.initAnchors();
                sitemaster.initInPageNav();
                sitemaster.initTables();
            });
            request.fail(function(jqXHR, textStatus) {
                $("#scan_ajax").html("Request failed... please reload the page");
            });
        </script>
        <div id="scan_ajax">
            <img src="<?php echo $base_url . 'www/images/loading.gif' ?>" />
            <p>
                Please wait while we load the latest scan.  This should be pretty quick.
            </p>
        </div>
        <?php
    } else {
        ?>
        <p>
            No scans found
        </p>
        <?php
    }
    ?>
</div>
