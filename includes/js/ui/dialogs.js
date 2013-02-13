includeCore('ui/base');
includeCore('libs/jquery-ui-1.9.2.position');
S.WDialog=S.extClass(S.Widget,{
	ctor:function(title,htmlOrText,buttons,options){
		options=S.extObj({
			open:true,
			closeOnEscape:true,
			zIndex:9000 //fancybox : 8030
		},options);
		this.elt=$('<div>')[S.isString(htmlOrText)?'text':'html'](htmlOrText)
				.addClass('ui-dialog-content ui-widget-content');
		
	},
	dispose:function(){
		this.uiDialog.remove();
	},
	_createWrapper:function(){
		this.uiDialog = $( "<div>" )
			.addClass( uiDialogClasses + this.options.dialogClass )
			.hide()
			.attr({
				// setting tabIndex makes the div focusable
				tabIndex: -1,
				role: "dialog"
			})
		.appendTo('body');
	}
});


S.dialogs={
	newAlert:function(title,message){
		var dialog,buttons={};
		buttons[i18nc['Close']]=function(){dialog.close();};
		dialog=new S.WDialog(title,message,buttons,{
			position:{my:'center top+99',at:'center top'},
			width:450,
			modal:true
		});
	},
	
	alert:function(title,message){
		var div=$('<div>'),buttons={};
		buttons[i18nc['Close']]=function(){div.dialog('close');};
		S.isString(message) ? div.text(message) : div.html(message);
		div.dialog({
			autoOpen: true,
			title:title,
			position:{my:'center top+99',at:'center top'},
			width:450,
			modal:true,
			buttons:buttons,
			close:function(){ div.remove(); },
			zIndex:9000 //fancybox : 8030
		});
	},
	confirm:function(title,message,okButtonName,callbackOk,callbackCancel){
		var div=$('<div>'),buttons={};
		S.isString(message) ? div.text(message) : div.html(message);
		
		
		buttons[i18nc.Cancel]=function(){
			div.hide();
			callbackCancel&&callbackCancel();
			div.dialog('close');
		};
		buttons[okButtonName]=function(){
			div.html(S.imgLongLoading());
			callbackOk();
			div.dialog('close');
		};
		
		div.dialog({
			autoOpen:true,
			title:title,
			position:{my:'center top+99',at:'center top'},
			width:450,
			modal:true,
			buttons:buttons,
			close:function(){ div.remove(); },
			zIndex:9000 //fancybox : 8030
		});
	},
	
	prompt:function(title,message,okButtonName,defaultVal,callback){
		var div=$('<div>'),buttons={},findInput;
		if($.isFunction(defaultVal)){
			callback=defaultVal;
			defaultVal='';
		}
		S.isString(message) ? div.text(message) : div.html(message);
		if(S.isObj(defaultVal)){
			findInput='select';
			div.append($('<select class="wp100">').html(
				S.oImplode(defaultVal,function(k,v){ return '<option value="'+k+'">'+S.escape(v)+'</option>'; })
			));
		}else{
			findInput='input';
			div.append($('<input type="text" class="wp100">').val(defaultVal).keydown(function(e){
				if(e.keyCode == '13'){
					e.preventDefault();
					e.stopImmediatePropagation();
					div.dialog('close');
					callback($(this).val());
					return false;
				}
			}));
		}
		
		this.form(title,div,okButtonName,function(){ callback(div.find(findInput).val()) });
	},
	
	form:function(title,content,okButtonName,callback){
		var div=$('<div>'),buttons={};
		S.isArray(content) ? div.append.apply(div,content) : div.html(content);
		
		buttons[i18nc.Cancel]=function(){div.dialog('close');};
		buttons[okButtonName]=function(){
			div.hide();
			callback(div);
			div.dialog('close');
		};
		
		div.dialog({
			autoOpen: true,
			title:title,
			position:{my:'center top+99',at:'center top'},
			width:450,
			modal:true,
			buttons:buttons,
			close:function(){ div.remove(); },
			zIndex:9000 //fancybox : 8030
		});
	}
};
