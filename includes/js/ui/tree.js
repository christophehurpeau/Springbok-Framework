includeCore('ui/dialogs');
S.tree={
	prepare:function(id,url){
		var tree=$('#'+id);
		tree.find('a.action.add').click(function(){
			var li=$(this).closest('li'),ul=li.children('ul');
			if(!ul.length) ul=$('<ul>').appendTo(li);
			ul.append('<li>'+(true?'<span class="name" contenteditable="true"></span>':'')+'</li>');
		});
		tree.find('a.action.edit').click(function(){
			var li=$(this).closest('li'),a,span=li.children('span.name'),
					actionOk=$('<a href="#" class="action icon ok"></a>'),
					actions=li.find('.actions').sHide();
			if(!span.length){
				a=li.children('a:first');
				a.sHide().after(span=$('<span>').text(a.text()));
			}
			span.attr('contenteditable',true).after(actionOk.click(function(){
				//TODO Save
				var text=span.text();
				if(a){
					a.sShow().text(text);
					span.remove();
				}
				actionOk.remove();
				actions.sShow();
			}));
		});
		tree.find('a.action.delete').click(function(){ });
	}
};