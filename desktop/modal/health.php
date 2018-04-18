<?php
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogics = ChauffeEau::byType('ChauffeEau');
?>

<table class="table table-condensed tablesorter" id="table_healthopenenocean">
	<thead>
		<tr>
			<th>{{ID}}</th>
			<th>{{Nom}}</th>
			<th>{{Statut}}</th>
			<th>{{Puissance éstimé}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getName() . '</span></td>';
	$status = '<span class="label label-success" style="font-size : 1em;cursor:default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em;cursor:default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	echo '<td>';
	echo $eqLogic->getPuissance() . 'W';
	$cache = cache::byKey('ChauffeEau::Puissance::'.$eqLogic->getId());
	echo '<div id="GraphPuissance" onload="Graph('.$cache->getValue('[]').');" ></div>';
	echo '</td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
<script>

function Graph(puissance) {
	var Series = [{
		step: true,
		name: '{{Variation puissance}}',
		data: puissance,
		type: 'line',
		marker: {
			enabled: false
		},
		tooltip: {
			valueDecimals: 2
		},
	}];
	drawSimpleGraph('GraphPuissance', Series);
}
function drawSimpleGraph(_el, _serie) {
    new Highcharts.chart({
      	title:{
          text:"Simulation"
        },
        chart: {
            zoomType: 'x',
            renderTo: _el,
            height: 100,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
        },
        credits: {
            text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',
        },
        navigator: {
            enabled: false
        },
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
            valueDecimals: 2,
        },
	legend: {
		enabled:false
	},
        yAxis: {
            format: '{value}',
            showEmpty: false,
            showLastLabel: true,
            min: 0,
            labels: {
                align: 'right',
                x: -5
            }
        },
        series: _serie
    });
}
</script>
