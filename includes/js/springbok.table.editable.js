var HTableEditable=function(url){
	this.url=url;
};
HTableEditable.prototype.updateField=function(pk,input){
	var img=$('<img src="'+webdir+'img/ajax-roller.gif" style="position:absolute;right:2px;top:2px"/>'),$i=$(input).after(img);
	$i.parent().find('img,span.icon').remove();
	$.ajax(this.url,{data:{pk:pk,val:input.value},
		success:function(){
			img.remove();
			$i.after('<span class="icon tick" style="position:absolute;right:2px;top:2px"></span>');
		},
		error:function(){
			img.remove();
			$i.after('<span class="icon cross" style="position:absolute;right:2px;top:2px"></span>');
		}
	});
};
