includeCore('ui/_inputFollow');

S.InputBox=S.extClass(S.ui.InputFollow,{
	ctor:function(input){
		var t=this;
		this.createDiv().appendTo($('#page'));
		input
			.data('sInputBox',this)
			.bind('dispose',function(){ t.div.remove(); })
			.focus(function(){
				t.hasFocus=true;
				if(!t.div.is(':empty,:visible')) t.showDiv();
			}).blur(function(){
				t.hasFocus=false;
				setTimeout(function(){
					if(!hasFocus) t.hideDiv();
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
	hideDivResult:function(){
		this.active=false;
		return this.div.sHide();
	}
};


/* A mettre dans un autre fichier pour gérer les data-box
$document.on('focus','input[data-box]',function(){
	
});
*/
