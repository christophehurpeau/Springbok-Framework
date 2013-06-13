$.fn.sPosNear=function(isTop,isLeft){
	this.css({
		position : 'absolute',
		display  : 'none',
		'z-index': '10000'
	}).appendTo($('body'));
};
