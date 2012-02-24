(function(){
	S.app={
		name:'',version:1,
		routers:[]
	};
	
	// Create a new model
	S.Model=function(attributes,options){
		this._changed=false;
		this._previousAttributes=$.extend({},attributes);
	}
	
	function App(name,version){
		this.name=name;this.version=version;
		this.controllers=[];
		this.init=function(){
			$('body').html('<div id="container" class="Site">'
				+'<header>'+S.app.header+'</header>'
				+'<div id="page"></div>'
				+'<footer>'+S.app.footer+'</footer>'
				+'</div>');
			var menu=$('header menu');
			$.each(this.controllers,function(id,c){
				menu.append('<li><a class="'+id+'" href="'+c.href+'" onclick="return S.app.load(this)">'+c.title+'</a></li>');
			});
		}
		this.loadHtml=function(html){
			html=$(html);
			html.find('a:not([onclick])').not('[href^="javascript:"]').not('[href^="http://"]').not('[href^="https://"]')
			.click(function(){
				alert('Click !');
				return false;
			});
		}
	}
	
	
	function Controller(){
	}
}());