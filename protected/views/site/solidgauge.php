
<script type="text/javascript">

$(function () {

    var gaugeOptions = {

	    exporting: { enabled: false },
        chart: {
            type: 'solidgauge'
        },

        title: null,

        pane: {
            center: ['50%', '85%'],
            size: '140%',
            startAngle: -90,
            endAngle: 90,
            background: {
                backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || '#EEE',
                innerRadius: '60%',
                outerRadius: '100%',
                shape: 'arc'
            }
        },

        tooltip: {
            enabled: false
        },

        // the value axis
        yAxis: {
            stops: [
                [<?php echo $rango1/100;?>, <?php echo "'".$colorRango1."'"; ?>], // verde
                [<?php echo $rango2/100;?>, <?php echo "'".$colorRango2."'"; ?>], // amarillo
                [<?php echo $rango3/100;?>, <?php echo "'".$colorRango3."'"; ?>] // rojo
            ],
            lineWidth: 0,
            minorTickInterval: null,
            tickPixelInterval: 400,
            tickWidth: 0,
            title: {
                y: -70
            },
            labels: {
                y: 16
            }
        },

        plotOptions: {
            solidgauge: {
                dataLabels: {
                    y: 5,
                    borderWidth: 0,
                    useHTML: true
                }
            }
        }
    };

    $('#<?php echo $id;?>').highcharts(Highcharts.merge(gaugeOptions, {
        yAxis: {
            min: <?php echo $rango[0];?>,
            max: <?php echo $rango[1];?>,
            title: {
            	text: '<?php echo $titulo;?>',
            }
        },

        credits: {
            enabled: false
        },

        series: [{
            name: '<?php echo $titulo;?>',
            data: [0],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                    ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                       '<span style="font-size:12px;color:black"><?php echo $leyenda;?></span></div>'
            },
            tooltip: {
                valueSuffix: '<?php $leyenda;?>'
            }
        }]

    }));

    // Esta funcion es la que permite que se mueva el indicador del reloj.
    //
    setInterval(function () {
        var chart = $('#<?php echo $id;?>').highcharts(),
            point,
            newVal,
            inc;

        if (chart) {
            point = chart.series[0].points[0];

           
            var valor;
            if( $('#<?php echo $tag;?>').length ) //Si el elemento existe
            {
        		valor = parseFloat($('#<?php echo $tag;?>').text());

				if (valor > <?php echo $rango[1]?>) 
					{
						valor = <?php echo $rango[1]?>;
					}
        		
            } else {
        		valor = 0;
            } 
            point.update(valor);

        }

    }, 1000);
});

</script>

<div id="<?php echo $id;?>"
	style="width: 300px; height: 200px; float: left"></div>

