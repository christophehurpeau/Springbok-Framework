includeLib('waypoints');
$.fn.sticky=function(options){  
	var e=$(this),parent=e.parent();
	S.ready(function(){
		$.waypoints.settings.scrollThrottle = 30;
		e.waypoint(function(event, direction) {
			parent.toggleClass('sticky', direction === "down");
			event.stopPropagation();
		});
	});
};