<?php
  use SiteMaster\Core\Config;
  $savvy->loadScript(Config::get('URL') . 'www/js/vendor/chart.min.js');
  $savvy->loadScriptDeclaration('
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

	$("#history_legend").html(chart.generateLegend());
  ');

?>
