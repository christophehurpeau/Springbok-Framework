var gmap={
	googlemap:null,coord:[],blocked:false,
	
	load:function(callbackName,sensor){
		$$.loadScript('http://maps.google.com/maps/api/js?sensor='+(sensor?'true':'false')+'&callback='+callbackName);
	},
	autozoom:function(coord,options){
		if(!coord) options.zoom=13;
		else{
			var min_lat=Number.MAX_VALUE,max_lat=Number.MIN_VALUE,min_lng=Number.MAX_VALUE,max_lng=Number.MIN_VALUE,zoom=0;
			for(var x in coord){
				min_lat=Math.min(min_lat,coord[x][2]);
				max_lat=Math.max(max_lat,coord[x][2]);
				min_lng=Math.min(min_lng,coord[x][3]);
				max_lng=Math.max(max_lng,coord[x][3]);
			}
			options.center=new google.maps.LatLng(min_lat + (max_lat - min_lat) / 2,min_lng + (max_lng - min_lng) / 2);
			
			var miles = (3958.75 * Math.acos(Math.sin(min_lat / 57.2958) * Math.sin(max_lat / 57.2958) + Math.cos(min_lat / 57.2958) * Math.cos(max_lat / 57.2958) * Math.cos(max_lng / 57.2958 - min_lng / 57.2958)));
			if(console) console.log(miles);
			if(miles < .05) zoom=17;
			else if(miles < .08) zoom=16;
			else if(miles < .15) zoom=15;
			else if(miles < 1) zoom=14;
			else if(miles < 2) zoom=13;
			else if(miles < 4) zoom=12;
			else if(miles < 10) zoom=11;
			else if(miles < 15 || !miles) zoom=10;
			else if(miles < 30) zoom=9;
			else if(miles < 50) zoom=8;
			else if(miles < 70) zoom=7;
			else if(miles < 200) zoom=6;
			else if(miles < 500) zoom=5;
			else zoom=2;
			options.zoom=zoom;
		}
	},
	init:function(lat,lng,coord,options){
		if(!options){
			options={
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				streetViewControl:false
			};
		}
		if(!options.zoom) this.autozoom(coord,options);
		else options.center=new google.maps.LatLng(lat,lng);
		this.googlemap=new google.maps.Map(document.getElementById("map_canvas"),options);
		if(coord){
			this.coord=new Array;
			$.each(coord,function(i,c){
				var marker = new google.maps.Marker({
					map:gmap.googlemap,
					position: new google.maps.LatLng(c[2],c[3]),
					title:c[1]
				});
				
				var infowindow = new google.maps.InfoWindow({ content: c[4] });
				google.maps.event.addListener(marker,'click',function(){ infowindow.open(gmap.googlemap,marker); });
				
				if(c[0]){
					gmap.coord[c[0]]={marker:marker,infowindow:infowindow};
					var div=$('div.'+c[0]).hover(function(){gmap.openInfo(c[0],true);},function(){gmap.closeInfo(c[0]);});
				}
			});
		}
	},
	openInfo:function(i,b){
		if(this.blocked!==false){ var blocked=this.blocked; this.blocked=false; this.closeInfo(blocked); }
		if(this.googlemap && this.coord && this.coord[i]) this.coord[i].infowindow.open(this.googlemap,this.coord[i].marker);
		if(!b) this.blocked=i;
	},
	closeInfo:function(i){
		if(this.blocked!==i && this.googlemap && this.coord && this.coord[i]) this.coord[i].infowindow.close();
	}
};