<?php
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
foreach ($context->site->getHistory(array('limit'=>100)) as $index=>$history) {
    $data['dates'][] = $history->date_created;
    $data['total_pages'][] = $history->total_pages;
    $data['gpa'][] = $history->gpa;
}

?>

<div class="graph-container wdn-grid-set">
    <canvas id="history_chart" class="wdn-col-one-half"></canvas>
    <div class="legend-container wdn-col-one-half">
        <div id="history_legend"></div>
    </div>
</div>
<script>
    var data = {
        labels: <?php echo json_encode($data['dates']) ?>,
        datasets: [
            {
                label: "Site GPA",
                fillColor: "rgba(220,220,220,0.2)",
                strokeColor: "rgba(220,220,220,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: <?php echo json_encode($data['total_pages']) ?>
            },
            {
                label: "Total Pages",
                fillColor: "rgba(151,187,205,0.2)",
                strokeColor: "rgba(151,187,205,1)",
                pointColor: "rgba(151,187,205,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: <?php echo json_encode($data['gpa']) ?>
            }
        ]
    };

    var ctx = document.getElementById("history_chart").getContext("2d");
    var chart = new Chart(ctx).Line(data, {
        responsive: true,
        maintainAspectRatio: false,
        datasetFill: false,
        legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span class=\"color\" style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",

        tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
        multiTooltipTemplate: "<%if (datasetLabel){%><%=datasetLabel%>: <%}%><%= value %>",
    });
    //console.log(chart.generateLegend());
    WDN.jQuery("#history_legend").html(chart.generateLegend());
</script>

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
