includeCore('ui/_inputFollow');
includeCore('libs/jquery-ui-1.9.2.position');

S.ui.InputBox=S.ui.InputFollow.extend({
	ctor:function(input){
		var t=this;
		S.ui.InputFollow.call(t,input);
		this.initDiv();
		if(this.hasFocus=input.is(':focus')) t.showDiv();
		input
			.data('sInputBox',this)
			.bind('dispose',function(){ t.dispose(); })
			.focus(function(){
				t.div.data('currentFocus',input);
				t.hasFocus=true;
				if(!t.div.is(':empty'/*,:visible'*/)) t.showDiv();
			}).blur(function(){
				if(t.div.data('currentFocus')===input){
					t.div.removeData('currentFocus')
					setTimeout(function(){
						if(!t.hasFocus && !t.div.data('currentFocus')) t.hideDiv();
					},200);
				}
				t.hasFocus=false;
			});
		;
	},
	initDiv:function(){ this.div=this.createDiv().appendTo($('#page')); },
	createDiv:function(){ return $('<div class="widget divInputBox hidden"/>'); },
	showDiv:function(){
		this.active=true;
		//var offsetParent=this.input/*.offsetParent()*/.closest('.col,.context,#page,body'),offsetParentPosition=offsetParent.position(),divPosition;
		var $window=$(window),divOffset;
		return this.div.css('min-width',this.input.width()+'px').sShow()
			.position({my:"left top",at:"left bottom",of:this.input,collision:"none"})
			.css({
				'max-width':(/*offsetParentPosition.left+*/$window.width()-(divOffset=this.div.offset()).left-10)+'px',
				'max-height':(/*offsetParentPosition.top+*//*offsetParent*/$window.height()-divOffset.top-10)+'px'
			});
	},
	hideDiv:function(){
		this.active=false;
		return this.div.sHide();
	},
	dispose:function(){
		this.div&&this.div.remove();
		S.ui.InputBox.super_.dispose.call(this);
	}
});