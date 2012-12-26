includeCore('ui/dialogs');
S.tree={
	prepare:function(id,url){
		var tree=$('#'+id);
		tree.children('li').each(function(li){
			console.log(li);
		});
	}
};