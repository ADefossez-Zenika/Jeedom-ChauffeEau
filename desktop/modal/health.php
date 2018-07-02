<?php
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogics = ChauffeEau::byType('ChauffeEau');
?>

<table class="table table-condensed tablesorter" id="table_healthChauffeEau">
	<thead>
		<tr>
			<th>{{ID}}</th>
			<th>{{Nom}}</th>
			<th>{{Statut}}</th>
			<th>{{Puissance estimée}}</th>
			<th>{{Ratio (duréee/deltaT°)}}</th>
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
	$cache = cache::byKey('ChauffeEau::Puissance::'.$eqLogic->getId());
	echo '<div class="Graph" id="Graph_Puissance_'. $eqLogic->getId().'" data-graph="'.$cache->getValue('[]').'" data-title="'.$eqLogic->getPuissance() . 'W"></div>';
	echo '</td>';
	echo '<td>';
	$cache = cache::byKey('ChauffeEau::Ratio::'.$eqLogic->getId());
	$RatioMoy = json_decode($cache->getValue('[]'), true);
	if(count($RatioMoy) > 0)
		$RatioMoy = round(array_sum($RatioMoy)/count($RatioMoy),0)
	else
		$RatioMoy=0;
	echo '<div class="Graph" id="Graph_Ratio_'. $eqLogic->getId().'" data-graph="'.$cache->getValue('[]').'" data-title="'.$RatioMoy . 's/°C"></div>';
	echo '</td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
<script>
$(function(){
	$('.Graph').each(function(){
		var json= $.parseJSON($(this).attr('data-graph'));
		var Series = [{
			step: false,
			name: $(this).attr('data-title'),
			data: json,
			type: 'line',
			marker: {
				enabled: false
			},
			tooltip: {
				valueDecimals: 2
			},
		}];
		if(json.length > 0)
			drawSimpleGraph($(this).attr('id'),$(this).attr('data-title'), Series);
	});
});
function drawSimpleGraph(_el,_name, _serie) {
    new Highcharts.chart({
      	title:{
          text:_name
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
        navigation:{
          buttonOptions:{
          	enabled:false
          }
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
            labels: {
                align: 'right',
                x: -5
            }
        },
        series: _serie
    });
}
</script>
