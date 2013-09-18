includeCoreUtils('UString/normalize');
includeCore('ui/_inputFollow');
includeCore('libs/jquery-ui-1.9.2.position');

S.ui.InputSearch=S.ui.InputFollow.extend({
	writable:{
		navigate:true, minLength:3, dataType:'json',delay:180,
		
		onSuccess:function(data){
			!data||data.length===0 ? this.emptyResult() : this.success(data);
		},
		reset:function(){
			this._div().empty();
		},
		success:function(data){
			this._div().html(this.display(data));
		},
		emptyResult:function(){
			this._div().empty();
		},
		_div:function(){ return this.div; }
	},
	ctor:function(input,url,destContent,options){
		S.ui.InputFollow.call(this,input);
		UObj.extend(this,options);
		this.div=destContent;
		this.display=this.display||S.ui.InputSearch.defaultDisplayList;
		
		var t=this,xhr,lastVal=null,currentTimeout;
		if(S.isFunc(url)) this.onChange=url;
		else if(S.isArray(url) || S.isObj(url) || url instanceof $){
			var list=url,filter=undefined,listValues;
			
			if(S.isObj(url)){
				if(url instanceof $){
					list=url.find('option').each(function(i,option){
						option=$(option);
						option.attr('data-value-normalized',UString.normalize(option.attr('value')));
					});
					filter=function(matcher){
						return !matcher ? list : list.filter(function(){ return matcher.test($(this).attr('data-value-normalized')); });
									//.map(function(){ return $(this).attr('value'); }).get();
					};
					listValues=null;
					this.displayLi=this.displayLi||function(v){ return $(v).attr('value'); };
				}else{
					list=url.list;
					if(S.isObj(list)){
						this.oKey=url.key;
						list=[]; listValues=[];
						UObj.forEach(url.list,function(k,v){ list.push(v); listValues.push(UString.normalize(v[url.key])); });
						filter=function(matcher){ return !matcher ? list : list.filter(function(v,k){ return matcher.test(listValues[k]); }); };
					}
				}
			}
			
			if(filter===undefined) filter=function(matcher){ return list.filter(function(v){ return matcher.test(v); }); };
			if(listValues===undefined) listValues=list.map(UString.normalize);
			
			this.onChange=function(term){
				var matcher = new RegExp( UString.normalize(term) ), data=filter(matcher);
				if(data) t.onSuccess(data);
			};
		}else{
			/*#if DEV*/if(!this.minLength) S.error('minLength=0 with url is not recommanded'); /*#/if*/
			this.onChange=function(val){
				if(xhr){ xhr.abort(); xhr=null;}
				if(currentTimeout) clearTimeout(currentTimeout);
				currentTimeout=setTimeout(function(){
					if(t.isNotEditable()) return;
					xhr=$.ajax({
						url:url,
						data:{term:val},
						dataType:t.dataType,
						success:function(data){ t.onSuccess(data); /* don't let other arguments */ },
						error:function(){ lastVal=null; (t.error||t.reset).call(t); }
					});
				},t.delay);
			};
		}
		
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
			}).bind('keyup focus',function(e){
				//e.stopPropagation();
				var val=input.val();
				input.trigger('sSearch',[val]);
			}).bind('sSearch',function(e,val){
				if(t.isNotEditable()) return;
				if(val===undefined) val=t.input.val();
				val=val.trim();
				if(t.navigate) S.history.navigate(url+'/'+val);
				if(/*!val ||*/ t.minLength && val.length < t.minLength){ t.reset(); lastVal=null; }
				else if(val!=lastVal){
					lastVal=val;
					t.onChange(val);
				}
			});
		if(this.hasFocus) input.trigger('sSearch');
	},
},{
	defaultDisplayList:function(data,ulAttrs,callback){
		var t=this,li,result=$('<ul>').attr(ulAttrs),key=this.oKey||'text';
		callback=callback||t.displayLi;
		$.each(data,function(i,v){
			li=$('<li/>');
			if(S.isString(v)) li.html(v);
			else{
				/*#if DEV*/if(!callback && !v[key]) console.warn('[ui/InputSearch:displayList]','text is empty',v,key);/*#/if*/
				li[t.escape===false?'html':'text'](callback ? callback(v,i): v.url ? $('<a/>').attr('href',v.url).text(v[key]) : v[key]).data('item',v);
			}
			result.append(li);
		});
		return result;
	},
});
