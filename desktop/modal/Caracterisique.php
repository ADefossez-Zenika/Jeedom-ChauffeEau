<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$eqLogic=eqLogic::byId(init('id'));
if(is_object($eqLogic))
	sendVarToJS('CartoChauffeEau', $eqLogic->getCartoChauffeEau());
?>
<div id="CartoChauffeEau"></div>
<script>
var data = new Array();
for(loop = 0;loop <CartoChauffeEau[1].length;loop++){
	data[loop]=[CartoChauffeEau[0][loop],CartoChauffeEau[1][loop]]
}
var Series = [{
    step: false,
    name: '{{Caracterisitque du chauffe-eau}}',
    data: data,
    type: 'line',
    marker: {
      enabled: false
    },
    tooltip: {
      valueDecimals: 2
    },
  }];
if(CartoChauffeEau.length > 0)
    drawSimpleGraph('CartoChauffeEau','{{Caractéristique du chauffe-eau}}', Series,'{{Température (°C)}}','{{Delta (°C)}}');
function drawSimpleGraph(_el,_name, _serie,_xLabel,_yLabel) {
    new Highcharts.chart({
      	title:{
          text:_name
        },
        chart: {
            zoomType: 'x',
            renderTo: _el,
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
        xAxis: {
		title: {
		    text: _xLabel
		},
        },
        yAxis: {
		title: {
		    text: _yLabel
		},
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
