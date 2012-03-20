/* http://documentcloud.github.com/backbone/backbone.js */
(function($){
	var historyStarted=false;
	S.history={
		options:{pushState:true},
		hashStrip:/^#*/,isIE:/msie [\w.]+/,interval:50,
		
		start:function(){
			if (historyStarted) throw new Error("history has already been started");
			this.options					= $.extend({}, {root: ''}, this.options);
			this._wantsPushState	= !!this.options.pushState;
			this._hasPushState		= !!(this.options.pushState && window.history && window.history.pushState);
			var fragment					= this.getFragment();
			var docMode					 = document.documentMode;
			var oldIE						 = (this.isIE.exec(navigator.userAgent.toLowerCase()) && (!docMode || docMode <= 7));
			if(oldIE){
				this.iframe = $('<iframe src="javascript:0" tabindex="-1" />').hide().appendTo('body')[0].contentWindow;
				this.navigate(fragment);
			}
			
			// Depending on whether we're using pushState or hashes, and whether
			// 'onhashchange' is supported, determine how we check the URL state.
			if(this._hasPushState) $(window).bind('popstate', this.checkUrl);
			else if('onhashchange' in window && !oldIE) $(window).bind('hashchange', this.checkUrl);
			else setInterval(this.checkUrl, this.interval);
			
			// Determine if we need to change the base url, for a pushState link
			// opened by a non-pushState browser.
			this.fragment = fragment;
			historyStarted = true;
			var loc = window.location;
			var atRoot	= loc.pathname == this.options.root;
			if (this._wantsPushState && !this._hasPushState && !atRoot) {
				this.fragment = this.getFragment(null, true);
				window.location.replace(this.options.root + '#' + this.fragment);
				// Return immediately as browser will do redirect to new url
				return true;
			} else if (this._wantsPushState && this._hasPushState && atRoot && loc.hash) {
				this.fragment = loc.hash.replace(this.hashStrip, '');
				window.history.replaceState({},document.title,loc.protocol + '//' + loc.host + this.options.root + this.fragment);
			}
			
			return this._hasPushState?true:this.loadUrl();
		},
		
		getFragment:function(fragment, forcePushState){
			if(fragment == null){
				if(this._hasPushState || forcePushState){
					fragment=window.location.pathname;
					var search=window.location.search;
					if(search) fragment+=search;
					if(fragment.indexOf(this.options.root) == 0) fragment = fragment.substr(this.options.root.length);
				}else fragment = window.location.hash;
			}
			return decodeURIComponent(fragment.replace(this.hashStrip, ''));
		},
		
		// Checks the current URL to see if it has changed, and if it has,
		// calls `loadUrl`, normalizing across the hidden iframe.
		checkUrl:function(e){
			var hist=S.history;
			var current = hist.getFragment();
			if(current == hist.fragment && hist.iframe) current = hist.getFragment(hist.iframe.location.hash);
			if(current == hist.fragment || current == decodeURIComponent(hist.fragment)) return false;
			if(hist.iframe) hist.navigate(current);
			hist.loadUrl() || hist.loadUrl(window.location.hash);
		},
		
		// Attempt to load the current URL fragment.
		loadUrl:function(fragmentOverride,state){
			var fragment = this.fragment = this.getFragment(fragmentOverride);
			if(fragment){
				var a=$('a[href="'+fragment+'"]');
				a.length===0 ? S.redirect(fragment) : a.click();
			}
		},
		
		navigate:function(fragment,state){
			var frag = (fragment || '').replace(this.hashStrip, '');
			if(frag.substr(0,1)==='?') frag=window.location.pathname+frag;
			if(window._gaq!==undefined) _gaq.push(['_trackPageview',frag]);
			if(this.fragment == frag || this.fragment == decodeURIComponent(frag)) return;
			if(this._hasPushState){
				var loc = window.location;
				if (frag.indexOf(this.options.root) != 0) frag = this.options.root + frag;
				this.fragment = frag;
				/*if(console && console.log) console.log('push: '+loc.protocol + '//' + loc.host + frag);*/
				window.history.pushState({}, document.title, loc.protocol + '//' + loc.host + frag);
			}else{
				window.location.hash = this.fragment = frag;
				if(this.iframe && (frag != this.getFragment(this.iframe.location.hash))){
					this.iframe.document.open().close();
					this.iframe.location.hash = frag;
				}
			}
		}
	};
})(jQuery);