includeCore('ui/dialogs');
includeCore('libs/jquery.sortable');
S.tree={
	prepare:function(id,url){
		var tree=$('#'+id); url+='/';
		tree.find('a.action.add').click(function(){
			var li=$(this).closest('li'),ul=li.children('ul');
			if(!ul.length) ul=$('<ul>').appendTo(li);
			ul.append('<li draggable="true"'
				+' ondragstart="event.dataTransfer.setData(\'text/plain\',\'This text may be dragged\')"'
				+'>'+(true?'<span class="name" contenteditable="true"></span>':'')+'</li>');
		});
		tree.find('a.action.edit').click(function(){
			var li=$(this).closest('li'),a,span=li.children('span.name'),
					actionOk=$('<a href="#" class="action icon ok"></a>'),
					actions=li.find('.actions').sHide();
			if(!span.length){
				a=li.children('a:first');
				a.sHide().after(span=$('<span>').text(a.text()));
			}
			span.attr('contenteditable',true).focus().after(actionOk.click(function(){
				actionOk.sHide();
				var text=span.text();
				$.post(url+'edit/'+li.data('id'),{text:text},function(data){
					if(a){
						a.sShow().text(text);
						span.remove();
					}
					actionOk.remove();
					actions.sShow();
				});
			}));
		});
		tree.find('a.action.delete').click(function(){ });
	}
};