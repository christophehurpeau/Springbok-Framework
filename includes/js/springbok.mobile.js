includeLib('jquery.mobile');
$(document).bind("mobileinit", function(){
	$.mobile.ajaxEnabled=$.mobile.hashListeningEnabled=$.mobile.pushStateEnabled=false;
});