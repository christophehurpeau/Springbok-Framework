$.widget( "ui.combobox", {
	_create: function() {
		var self = this,
			orgWidth=this.element.width(),
			select = this.element.hide(),
			selected = select.children( ":selected" ),
			value = selected.val() ? selected.text() : "";
		var input = this.input = $( "<input>" ).addClass('ui-combobox ui-corner-left')
			.insertAfter( select )
			.val( value ),countOptions=select.children( "option" ).length;
		if(orgWidth) input.css({'width':orgWidth});
		input.autocomplete({
				delay: 0,
				minLength:countOptions > 50 ? (countOptions > 1000 ? 3 : 2) : 0,
				source: function( request, response ) {
					var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
					response( select.children( "option" ).map(function() {
						var text = $( this ).text();
						if ( !request.term || matcher.test(text) )
							return {
								label: text.replace(
									new RegExp(
										"(?![^&;]+;)(?!<[^<>]*)(" +
										$.ui.autocomplete.escapeRegex(request.term) +
										")(?![^<>]*>)(?![^&;]+;)", "gi"
									), "<strong>$1</strong>" ),
								value: text,
								option: this
							};
					}) );
				},
				select:function(event,ui){
					ui.item.option.selected = true;
					self._trigger( "selected", event, {
						item: ui.item.option
					});
					var $option=$(ui.item.option);
					if($option.is('[onclick]')) $option.click();
				},
				change: function( event, ui ) {
					if ( !ui.item ) {
						var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
							valid = false;
						select.children( "option" ).each(function() {
							if ( $( this ).text().match( matcher ) ) {
								this.selected = valid = true;
								return false;
							}
						});
						if ( !valid ) {
							// remove invalid value, as it didn't match anything
							$( this ).val( "" );
							select.val( "" );
							input.data( "autocomplete" ).term = "";
							return false;
						}
					}
				}
			});

		input.data( "autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.label + "</a>" )
				.appendTo( ul );
		};

		this.button = $( "<button type='button'>&nbsp;</button>" )
			.attr( "tabIndex", -1 )
			.attr( "title", "Show All Items" )
			.insertAfter( input )
			.button({
				icons: {
					primary: "ui-icon-triangle-1-s"
				},
				text: false
			})
			.removeClass( "ui-corner-all" )
			.addClass( "ui-combobox ui-corner-right ui-button-icon" )
			.click(function() {
				// close if already visible
				if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
					input.autocomplete( "close" );
					return;
				}

				// work around a bug (likely same cause as #5265)
				$( this ).blur();

				// pass empty string as value to search for, displaying all results
				if(countOptions < 50) input.autocomplete( "search", "" );
				input.focus();
			});
	},

	destroy: function() {
		this.input.remove();
		this.button.remove();
		this.element.show();
		$.Widget.prototype.destroy.call( this );
	}
});

var ajaxC_CommonFunction=function(div,url,options,prepare,onAdd){
	url+='/'; options=options || {}; options.url=options.url || '';
	var select=prepare(div,options), t, o, input=div.find('input');
	
	div.find('a.action.add').click(function(e){
		e.preventDefault();
		var val=select&&select.val(),action,data={};
		if(!val){
			if(!options.allowNew){ alert(i18nc['This field is required']); return false; }
			action='create';
			data={val:input.val()};
			if(S.isFunc(options.allowNew)){
				options.allowNew.call(this,val,function(d){onAdd(select,action,data,d,val)},input,url,options);
				return false;
			}
		}else action='add/'+val;
		
		$.get(url+action+options.url,data,function(d){
			if(d=='1' || (action=='create' && $.isNumeric(d))){
				onAdd(select,action,data,d,val);
				input.val('');
			}
		});
		return false;
	});
	input.keydown(function(e){
		if(e.keyCode == '13'){
			e.preventDefault();
			e.stopImmediatePropagation();
			div.find('a.action.add').click();
			return false;
		}
	});
	return select;
};


var ajaxCRDCommonFunction=function(div,url,options,prepare,onDelete,onAdd){
	options=options || {}; options.url=options.url || '';
	var ul=div.find('ul'),select=ajaxC_CommonFunction(div,url,options,prepare,function(select,action,data,d,val){
		var t;
		if(action=='create'){
			t=data.val;
			val=d
		}else{
			t=onAdd(select,val);
		}
		if(!(ul.has('li[rel='+val+']').length)){
			$('<li style="display:none;opacity:0"/>').attr('rel',val).html($('<span/>').text(t))
				.append(' <a href="#" class="icon action delete"></a>').appendTo(ul).animate({opacity:1,height:'toggle'},'slow');
			ul.change();
		}/* DEV */else alert('already has this line !'); /* /DEV */
	});
	ul.on('click','a.delete',function(e){
		e.preventDefault();
		var li=$(this).closest('li'),val=li.attr('rel');
		$.get(url+'/del/'+val+options.url,function(d){
			if(d=='1'){
				onDelete(select,li,val)
				li.animate({opacity:0,height:'toggle'},'slow',function(){li.remove();ul.change();});
			}
		});
		return false;
	});
};


$.fn.ajaxCRDSelectFiltrable=function(url,options){
	ajaxCRDCommonFunction(this,url,options,function(div){
		return div.find('select:first').combobox();
	},function(select,li,val){
		select.append($('<option/>').attr('value',val).text(li.find('span:first').text()));
	},function(select,val){
		var o=select.find('option[value="'+val+'"]'),t=o.text();
		o.remove();
		return t;
	})
};

$.fn.ajaxCRDInputAutocomplete=function(url,options){
	ajaxCRDCommonFunction(this,url,options,function(div,options){
		var input=div.find('input'),hidden=$('<input type="hidden"/>');
		input.after(hidden);
		input.autocomplete({
			source:url+'/autocomplete'+options.url,
			minLength:1,
			select:function(event,ui){ hidden.val(ui.item.id).data('name',ui.item.name); input.val(ui.item.name); return false; }
		}).data( "autocomplete" )._renderItem = function(ul,item){
			return $( "<li/>" ).data( "item.autocomplete", item )
				.append( $('<a/>').text(item.name) )
				.appendTo( ul );
		};
		return hidden;
	},function(select,li,val){
		select.val('');
	},function(select,val){
		return select.data('name');
	});
};

/* Create - Select */
$.fn.ajaxCSInputAutocomplete=function(url,options,redirect){
	ajaxC_CommonFunction(this,url,options,function(div,options){
		var input=div.find('input'),hidden=$('<input type="hidden"/>');
		input.after(hidden);
		input.autocomplete({
			source:url+'/autocomplete'+options.url,
			minLength:1,
			select:function(event,ui){ S.redirect(redirect?redirect(ui.item):ui.item.url); return false; }
		}).data( "autocomplete" )._renderItem = function(ul,item){
			return $( "<li/>" ).data( "item.autocomplete", item )
				.append( $('<a/>').text(item.name) )
				.appendTo( ul );
		};
	},function(select,action,data,d,val){
		if(action=='create'){
			S.redirect(redirect?redirect({id:d}):url+'/view/'+d);
		}
	});
};


$.fn.toggleLink=function(dest){
	if(S.isString(dest)) dest=$(dest);
	var t=this.css({padding:'8px 0 8px 20px',position:'relative',backgroundColor:'transparent'}),icon=$('<span class="ui-icon ui-icon-triangle-1-e"/>').css({position:'absolute',top:'50%',marginTop:'-8px',left:'2px'});
	this.find('a').css('outline','none');
	t.prepend(icon)
		.hover(function(){t.addClass('ui-state-hover')},function(){t.removeClass('ui-state-hover')})
		.click(function(){
			if(dest.is(':visible')){
				icon.addClass('ui-icon-triangle-1-se').removeClass('ui-icon-triangle-1-s');
				dest.slideUp('slow',function(){icon.addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-se')});
			}else{
				icon.addClass('ui-icon-triangle-1-se').removeClass('ui-icon-triangle-1-e');
				dest.slideDown('slow',function(){icon.addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-se')});
			}
			return false;
		})
		;
	return this;
};