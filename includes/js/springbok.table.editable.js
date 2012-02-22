var HTableEditable=function(url){
	this.url=url;
};
HTableEditable.prototype.updateField=function(name,pk,input){
	var img=$('<img src="'+webdir+'img/ajax-roller.gif" style="position:absolute;right:2px;top:2px"/>'),$i=$(input).after(img),val;
	$i.parent().find('img,span.icon').remove();
	
	if($i.is(':checkbox')) val=$i.is(':checked') ? '' : undefined;
	else val=input.value;
	$.ajax(this.url,{data:{pk:pk,name:name,val:val},
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
