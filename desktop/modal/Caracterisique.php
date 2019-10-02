<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$eqLogic=eqLogic::byId(init('id'));
if(is_object(eqLogic))
  sendVarToJS('CartoChauffeEau', $eqLogic->getCartoChauffeEau());
?>
<div class="CartoChauffeEau"></div>
<script>
var Series = [{
    step: false,
    name: '{{Caracterisitque du chauffe-eau}}',
    data: CartoChauffeEau,
    type: 'line',
    marker: {
      enabled: false
    },
    tooltip: {
      valueDecimals: 2
    },
  }];
if(CartoChauffeEau.length > 0)
    drawSimpleGraph($('.CartoChauffeEau'),'{{Caracterisitque du chauffe-eau}}', Series);
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
