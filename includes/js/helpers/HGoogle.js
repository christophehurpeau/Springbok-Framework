S.HGoogle={
	staticMap:function(size,zoom,center_or_path,markers,mapType){
		var center,path,url='';
		if(S.isArray(center_or_path)){ center=false; path=center_or_path }
		else{ center=center_or_path; path=false; }
		
		if(center) url+='&center='+center;
		if(path) url+='&path='+path.join('|');
		if(zoom) url+='&zoom='+zoom;
		if(markers){
			if(markers.multiple) delete markers.multiple;
			else markers=[markers];
			markers.forEach(function(marker){
				url+='&markers=';
				if(S.isObj(marker)){
					url+=marker.style.join('|')+'|';
					marker=marker.locations;
				}
				url+=marker.join('|');
			});
		}
		url+='&size='+size+(mapType?'&maptype='+mapType:'');
		return 'http://maps.googleapis.com/maps/api/staticmap?sensor=false'+url;
	}
};
