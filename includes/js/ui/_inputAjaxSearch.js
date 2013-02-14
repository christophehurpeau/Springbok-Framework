includeCore('ui/_inputFollow');
includeCore('libs/jquery-ui-1.9.2.position');

S.ui.InputSearch=S.ui.InputFollow.extend({
	navigate:true, minLength:3, dataType:'json',delay:180,
	ctor:function(input,url,destContent,options){
		S.ui.InputFollow.call(this,input);
		S.extObj(this,options);
		this.div=destContent;
		this.display=this.display||S.ui.InputSearch.defaultDisplayList;
		
		var t=this,xhr,lastVal='',currentTimeout;
		if(S.isFunc(url)) this.onChange=url;
		else if(S.isArray(url) || S.isObject(url)){
			var list=url,filter,listValues;
			
			filter=function(matcher){ return list.filter(function(v){ return matcher.test(v) }); };
			
			if(S.isObject(url)){
				list=url.list;
				if(S.isObject(list)){
					this.oKey=url.key;
					list=[]; listValues=[];
					S.oForEach(url.list,function(k,v){ list.push(v); listValues.push(S.sNormalize(v[url.key])) });
					filter=function(matcher){ return list.filter(function(v,k){ return matcher.test(listValues[k]) }); };
				}
			}
			
			if(listValues===undefined) listValues=list.map(S.sNormalize);
			
			this.onChange=function(term){
				var matcher = new RegExp( S.sNormalize(term) ), data=filter(matcher);
				if(data) t.success(data);
			}
		}else this.onChange=function(val){
			if(xhr){ xhr.abort(); xhr=null;}
			if(currentTimeout) clearTimeout(currentTimeout);
			currentTimeout=setTimeout(function(){
				if(t.isNotEditable()) return;
				xhr=$.ajax({
					url:url,
					data:{term:val},
					dataType:options.dataType,
					success:function(data){ t.success(data); /* don't let other arguments */ },
					error:(t.error||t.reset).bind(t)
				});
			},options.delay);
		};
		
		input.attr('autocomplete','off')
			// turning off autocomplete prevents the browser from remembering the
			// value when navigating through history, so we re-enable autocomplete
			.keydown(function(e){
				var eKeyCode=e.keyCode;
				if(
					(eKeyCode>=keyCodes.SHIFT && eKeyCode<=keyCodes.CAPS_LOCK)
					|| (eKeyCode>=keyCodes.PAGE_UP && eKeyCode<=keyCodes.HOME)
				) return;
				if(t.keydown && t.keydown(eKeyCode)===false){
					e.stopPropagation(); e.preventDefault(); //usefull for autocomplete
					return false;
				}
			}).keyup(function(e){
				var val=input.val();
				input.trigger('sSearch',[val])
			}).bind('sSearch',function(e,val){
				if(t.isNotEditable()) return;
				if(val===undefined) val=t.input.val();
				val=val.trim();
				if(t.navigate) S.history.navigate(url+'/'+val);
				if(!val || val.length < t.minLength) t.reset();
				else if(val!=lastVal){
					lastVal=val;
					t.onChange(val);
				}
			});
	},
	reset:function(){
		this.div.empty();
	},
	success:function(data){
		this.div.html(this.display(data,undefined));
	}
},{
	defaultDisplayList:function(data,ulAttrs,callback){
		var t=this,li,result=$('<ul>').attr(ulAttrs),key=this.oKey||'text';
		callback=callback||t.displayLi;
		$.each(data,function(i,v){
			li=$('<li/>');
			if(S.isString(v)) li.html(v);
			else{
				/* DEV */if(!callback && !v[key]) console.warn('[ui/InputSearch:displayList]','text is empty',v,key);/* /DEV */
				li[t.escape===false?'html':'text'](callback ? callback(v,i): v.url ? $('<a/>').attr('href',v.url).text(v[key]) : v[key]).data('item',v);
			}
			result.append(li);
		});
		return result;
	},
});