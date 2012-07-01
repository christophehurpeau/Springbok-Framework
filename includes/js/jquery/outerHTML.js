/* http://stackoverflow.com/questions/2419749/get-selected-elements-outer-html */
$.fn.outerHTML=function(){
	var t = this,r;
	if(!t.length) return undefined;
	return "outerHTML" in t[0] ? t[0].outerHTML : (r = t.wrap('<div>').parent().html(), t.unwrap(), r);
};