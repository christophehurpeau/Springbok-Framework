/* http://cmlenz.github.com/jquery-iframe-transport/ */
$.fn.sAjaxUploadFiles=function(success,options){
	return this.each(function(){
		var form=$(this),imgLoadingSubmit=S.imgLoading();
		options=options||{};
		
		form.submit(function(){
			var submit=form.find(':submit'),
				iframe=$("<iframe src='javascript:false;' name='"+form.attr('target')+"' style='display:none'></iframe>");
			
			form.fadeTo(180,0.4);
			submit.hide();submit.parent().append(imgLoadingSubmit);
			
			var firstload=false,onload=function(){
				$(this).unbind("load ready");
				var doc = this.contentWindow ? this.contentWindow.document :
					(this.contentDocument ? this.contentDocument : this.document),
					root = doc.documentElement ? doc.documentElement : doc.body,
					textarea = root.getElementsByTagName("textarea")[0],
					type = textarea ? textarea.getAttribute("data-type") : null,
					content = {
						html: root.innerHTML,
						text: type ?
							textarea.value :
							root ? (root.textContent || root.innerText) : null
					};
				if(content.text.charAt(0)=='{') try{ content.json=$.parseJSON(content.text); }catch(e){}
				iframe.attr("src", "javascript:false;").remove();
				imgLoadingSubmit.remove();form.fadeTo(150,1).reset();
				success(content.text,content.json,content,type);
				submit.show().blur();
			};
			
			iframe.bind("load",function(){
				if(firstload) onload.call(this);
				else{
					firstload=true;
					iframe.unbind("load").bind("load ready",onload);
				}
			});
			form.append(iframe);
			firstload=true;
			return true;
		});
	});
};