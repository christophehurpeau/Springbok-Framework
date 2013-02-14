includeCore('ui/_inputFollow');

S.ui.InputBox=S.ui.InputFollow.extend({
	ctor:function(input){
		var t=this;
		S.ui.InputFollow.call(t,input);
		this.div=this.createDiv().appendTo($('#page'));
		this.hasFocus=false;
		input
			.data('sInputBox',this)
			.bind('dispose',function(){ t.dispose(); })
			.focus(function(){
				t.hasFocus=true;
				if(!t.div.is(':empty,:visible')) t.showDiv();
			}).blur(function(){
				t.hasFocus=false;
				setTimeout(function(){
					if(!t.hasFocus) t.hideDiv();
				},200);
			});
		;
	},
	createDiv:function(){ return $('<div class="widget hidden"/>'); },
	showDiv:function(){
		this.active=true;
		return this.div.css('width',this.input.width()).sShow()
			.position({my:"left top",at:"left bottom",of:this.input,collision:"none"});
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


/* A mettre dans un autre fichier pour gérer les data-box
$document.on('focus','input[data-box]',function(){
	
});
*/
