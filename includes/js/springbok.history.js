/* http://documentcloud.github.com/backbone/backbone.js */
(function($){
	var historyStarted=false,routeStripper=/^[#\/]*/,isIE=/msie [\w.]+/;
	S.history={
		options:{pushState:true},interval:50,
		
		start:function(){
			if (historyStarted) throw new Error("history has already been started");
			historyStarted = true;
			this.options=UObj.extend({root:baseUrl.substr(1)},this.options);
			this._wantsPushState= !!this.options.pushState;
			this._hasPushState= !!(this.options.pushState && window.history && window.history.pushState);
			var fragment=this.getFragment(),docMode=document.documentMode,oldIE=(isIE.exec(navigator.userAgent.toLowerCase()) && (!docMode || docMode <= 7));
			if(oldIE){
				this.iframe = $('<iframe src="javascript:0" tabindex="-1" />').hide().appendTo('body')[0].contentWindow;
				this.navigate(fragment);
			}
			
			// Depending on whether we're using pushState or hashes, and whether
			// 'onhashchange' is supported, determine how we check the URL state.
			if(this._hasPushState) $(window).bind('popstate', this.checkUrl);
			else if(('onhashchange' in window) && !oldIE) $(window).bind('hashchange', this.checkUrl);
			else this._checkUrlInterval=setInterval(this.checkUrl, this.interval);
			
			// Determine if we need to change the base url, for a pushState link
			// opened by a non-pushState browser.
			this.fragment = fragment;
			var loc = window.location/*, atRoot=loc.pathname == this.options.root*/,hash;
			
			// If we've started off with a route from a `pushState`-enabled browser,
			// but we're currently in a browser that doesn't support it...
			/*if (this._wantsPushState && !this._hasPushState && !atRoot) {
				this.fragment = this.getFragment(null, true);
				window.location.replace(this.options.root + '#' + this.fragment);
				// Return immediately as browser will do redirect to new url
				return true;
			
			// Or if we've started out with a hash-based route, but we're currently
			// in a browser where it could be `pushState`-based instead...
			} else */if (this._wantsPushState && this._hasPushState && (hash=this.getHash())) {
				this.fragment = hash.replace(routeStripper, '');
				window.history.replaceState({fragment:this.fragment},document.title,loc.protocol + '//' + loc.host + baseUrl + this.fragment);
				return false;
			}
			return this._hasPushState || fragment===''?true:false;
		},
		
		getHash:function(windowOverride){
			var match = (windowOverride ? windowOverride.location : window.location).href.match(/#\/(.*)$/);
			return match ? match[1] : '';
		},
		getFragment:function(fragment, forcePushState){
			if(fragment == null){
				if(this._hasPushState || forcePushState){
					fragment=window.location.pathname;
					var search=window.location.search;
					if(search) fragment+=search;
					///if(fragment.indexOf(this.options.root) == 0) fragment = fragment.substr(this.options.root.length);
				}else fragment=this.getHash();
			}
			if(fragment.startsWith(baseUrl)) fragment=fragment.substr(baseUrl.length);
			else if(fragment.startsWith(this.options.root)) fragment=fragment.substr(this.options.root.length);
			return fragment.replace(routeStripper,'');
		},
		
		// Checks the current URL to see if it has changed, and if it has,
		// calls `loadUrl`, normalizing across the hidden iframe.
		checkUrl:function(e){
			var history=S.history, current = history.getFragment();
			if(current == history.fragment && history.iframe) current = history.getFragment(history.getHash(history.iframe));
			if(current == history.fragment) return false;
			if(history.iframe) history.navigate(current);
			history.loadUrl();
		},
		
		// Attempt to load the current URL fragment.
		loadUrl:function(fragmentOverride,state){
			var fragment = baseUrl+( this.fragment = this.getFragment(fragmentOverride));
			if(fragment){
				var a=$('a[href="'+fragment+'"]');
				a.length===0 ? S.redirect(fragment) : a.click();
			}
		},
		
		navigate:function(fragment,replace){
			var frag = (fragment || '').replace(routeStripper, ''),loc=window.location;
			if(frag.charAt(0)==='?') frag=loc.pathname+frag;
			if(this.fragment == frag) return;
			if(window._gaq) _gaq.push(['_trackPageview',frag]);
			
			if(frag.startsWith(baseUrl)) frag=frag.substr(baseUrl.length);
			else if(frag.startsWith(this.options.root)) frag=frag.substr(this.options.root.length);
			if(this.fragment == frag) return;
			
			this.fragment=frag;
			if(this._hasPushState){
				//if(console && console.log) console.log('push: '+loc.protocol + '//' + loc.host + baseUrl+frag);
				//var title=document.title;
				window.history[replace?'replaceState':'pushState']({},document.title, loc.protocol+'//'+loc.host + baseUrl+frag);
			}else{
				this._updateHash(loc,frag,replace);
				if(this.iframe && (frag != this.getFragment(this.iframe.location.hash))){
					if(!replace) this.iframe.document.open().close();
					this._updateHash(this.iframe.location,frag,replace);
				}
			}
		},
		_updateHash:function(location,fragment,replace){
			replace ? location.replace(location.toString().replace(/(javascript:|#).*$/, '') + '#/' + fragment) : location.hash = '/'+fragment; 
		}
	};
})(jQuery);