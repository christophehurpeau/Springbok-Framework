<?php
/**
 * @deprecated
 */
class HGraph{
	public static function months($id,$dataMonth,$colors="['#3B5A4A','#579575','#839557','#958C12','#C5B47F','#EAA228','#EDC240','#953579']"){
		$maxXAxis=strtotime(date('Y-m'/*-t'*/).'-01')*1000+(1000*60*60*24);
		$dataMonth=json_encode($dataMonth);
		return <<<JS
$.plot($("$id"),$dataMonth, {
	colors:$colors,
	grid:{hoverable:true},
	series:{ lines:{show:true}, points:{show:true} },
	xaxis:{ mode:"time", timeformat:"%b %y", minTickSize:[1,"month"], max:$maxXAxis, monthNames:['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] },
	yaxis:{ min:0 }
});
JS;
	}
	
	public static function days($id,$dataDays,$colors="['#3B5A4A','#579575','#839557','#958C12','#C5B47F','#EAA228','#EDC240','#953579']"){
		$maxXAxis=strtotime(date('Y-m-d'))*1000+(1000*60*60*12);
		$dataDays=json_encode($dataDays);
		//TODO : put weekendAreas in JS
		return <<<JS
function weekendAreas(axes) {
	var markings=[];
	var d=new Date(axes.xaxis.min);
	// go to the first Saturday
	d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
	d.setUTCSeconds(0);
	d.setUTCMinutes(0);
	d.setUTCHours(0);
	var i = d.getTime();
	do{
		// when we don't set yaxis, the rectangle automatically
		// extends to infinity upwards and downwards
		markings.push({ xaxis: { from: i-(1000*60*60*12), to: i + ((24 * 60 * 60) * 1000)+(1000*60*60*12) } });
		i += 7 * 24 * 60 * 60 * 1000;
	}while(i < axes.xaxis.max);

	return markings;
}
$.plot($("$id"),$dataDays, {
	colors:$colors,
	grid:{markings:weekendAreas,hoverable:true},
	//series:{ bars: { show: true, align:'center', barWidth: 1000*60*60*24, fill:true, fillColor: { colors: [ { opacity: 0.8 }, { opacity: 0.1 } ] } } },
	legend: { noColumns: 2 },
	series:{ lines:{show:true}, points:{show:true} },
	xaxis: { mode: "time", timeformat: "%0d %b", tickSize:[7, "day"], max:$maxXAxis, monthNames:['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] },
	yaxis:{ min:0 }
});
JS;
	}
}