includeCore('ui/_inputAjaxSearch');
includeCore('ui/inputbox');

S.ui.Autocomplete=S.ui.InputSearch.extend({
	writable:{ navigate:false, },
	ctor:function(input,url,options){
		var t=this;
		S.ui.InputBox.call(t,input,'sAutocomplete');
		if(S.isFunc(options)) options={displayCallback:options};
		S.ui.InputSearch.call(t,input,url,t.div,options);
		
		t.div.on('click','li',t.select ? function(){ t.select.call(t,$(this)); t.hideDiv().empty(); }
							 : function(){ input.val($(this).text()).change(); t.hideDiv().empty(); });
		t.div.on('mouseenter','li',function(){ t.div.find('li.current').removeClass('current'); });
	},
	createDiv:function(){ return $('<div class="divAutocomplete widgetBox widget hidden"/>'); },
	divFindLi:function(selector){
		return this.div.find('li'+selector);
	},
	
	success:function(data){
		this.div.html(this.display(data,{'class':'clickable spaced'}));
		this.showDiv();
	},
	error:function(data){
		this.hideDiv().empty();
	},
	
	keydown:function(eKeyCode){
		if(this.active){
			switch(eKeyCode){
				case keyCodes.ESCAPE:
					this.hideDiv();
					return false;
				case keyCodes.DOWN:
					var current=this.divFindLi('.current');
					if(current.length) current.removeClass('current').next().addClass('current');
					else this.divFindLi(':first').addClass('current');
					return false;
				case keyCodes.UP:
					var current=this.divFindLi('.current');
					if(current.length) current.removeClass('current').prev().addClass('current');
					else this.divFindLi(':last').addClass('current');
					return false;
				case keyCodes.ENTER: case keyCodes.NUMPAD_ENTER:
					this.divFindLi('.current').click();
					return false;
				case keyCodes.PAGE_UP: case keyCodes.HOME:
					this.divFindLi('.current').removeClass('current');
					this.divFindLi(':first').addClass('current');
					return false;
				case keyCodes.PAGE_DOWN: case keyCodes.END:
					this.divFindLi('.current').removeClass('current');
					this.divFindLi(':last').addClass('current');
					return false;
			}
		}else if(eKeyCode==keyCodes.UP){
			this.showDiv();
			return false;
		}
	}
});

S.mixin(S.ui.Autocomplete,S.ui.InputBox);

$.fn.sAutocomplete=function(url,options,displayResult){ return new S.ui.Autocomplete(this,url,options,displayResult); };
if(includedCore('helpers/HEltFInput')) S.HEltFInput.prototype.autocomplete=function(url,options,displayResult){
	new S.ui.Autocomplete(this.elt,url,options,displayResult);
	return this;
};