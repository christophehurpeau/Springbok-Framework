includeCore('ui/_inputAjaxSearch');

$.fn.sAjaxSearch=function(url,destContent,options){
	new S.ui.InputSearch(this,url,destContent,options);
	return this;
};