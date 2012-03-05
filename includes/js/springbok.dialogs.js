S.dialogs={
	alert:function(title,message){
		var div=$('<div/>');
		S.isString(message) ? div.text(message) : div.html(message);
		div.dialog({
		    autoOpen: true,
		    title:title,
		    position: ['center',150],
		    width:450,
		    modal:true,
		    zIndex:2000 //fancybox : 1000
		});
	},
	
	prompt:function(title,message,okButtonName,defaultVal,callback){
		var div=$('<div/>'),buttons={};
		if($.isFunction(defaultVal)){
			callback=defaultVal;
			defaultVal='';
		}
		S.isString(message) ? div.text(message) : div.html(message);
		div.append($('<input type="text" style="width:99%"/>').val(defaultVal).keydown(function(e){
			if(e.keyCode == '13'){
				e.preventDefault();
				e.stopImmediatePropagation();
				div.dialog( "close" );
				callback($(this).val());
				return false;
			}
		}));
		
		buttons[i18nc.Cancel]=function(){$(this).dialog( "close" );};
		buttons[okButtonName]=function(){
			$(this).dialog( "close" );
			callback(div.find('input').val());
		};
		
		div.dialog({
		    autoOpen: true,
		    title:title,
		    position: ['center',150],
		    width:450,
		    modal:true,
		    buttons:buttons,
		    zIndex:2000 //fancybox : 1000
		});
	},
	
	form:function(title,content,okButtonName,callback){
		var div=$('<div/>'),buttons={};
		div.html(content);
		
		buttons[i18nc.Cancel]=function(){$(this).dialog( "close" );};
		buttons[okButtonName]=function(){
			$(this).dialog( "close" );
			callback();
		};
		
		div.dialog({
		    autoOpen: true,
		    title:title,
		    position: ['center',150],
		    width:450,
		    modal:true,
		    buttons:buttons,
		    zIndex:2000 //fancybox : 1000
		});
	}
};
