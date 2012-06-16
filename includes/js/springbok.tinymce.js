S.tinymce={
	attrs:{},
	
	load:function(plugins){
		if(window.tinymce===undefined){
			S.loadSyncScript(webdir+'js/tinymce.js');
			S.loadSyncScript(webdir+'js/tinymce.'+i18n_lang+'.js');
			// bug for ajax partial load - document.ready should not be necessary, but we never know !
			/*S.ready(function(){tinymce.dom.Event._pageInit(window)});*/
		}
		return this;
	},
	
	init:function(w,h,barType,withImageManager){
		this.attrs={
			theme:'advanced',language:i18n_lang,
			skin:'o2k7',skin_variant:'black',
			
			theme_advanced_toolbar_location:'top', theme_advanced_toolbar_align:'left', theme_advanced_statusbar_location:'bottom',//  theme_advanced_resizing:false,
			document_base_url:basedir,
			width:w, height:h,
			
			font_size_style_values:"7pt,8pt,9pt,10pt,11pt,12pt,14pt,18pt,24pt,36pt",
			
			convert_urls:false,
			
			entity_encoding:'raw',
			convert_fonts_to_spans:true,
			
			style_formats:[
				{title:'Bold text',selector:'span,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'bold'},
				{title:'Italic text',selector:'span,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'italic'},
				{title:'Clear',selector:'span,p,h1,h2,h3,h4,h5,h6,div,ul,table,img', classes:'clear'},
				{title:'Clearfix',selector:'div', classes:'clearfix'},
			],
			formats:{
				alignleft:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignLeft'},
				bold:{inline:'strong'},italic:{inline:'i'},
				alignleft:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignLeft'},
				aligncenter:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'center'},
				alignright:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignRight'},
			},
			
			doctype : '<!DOCTYPE html>',
			verify_css_classes:true, apply_source_formatting:false,
			theme_advanced_styles:'Center=center;Clear=clear;Pointer=pointer;Smallinfo=smallinfo;Margin-Right 10=mr10;Margin-Right 20=mr20;Margin-Left 10=ml10;Margin-Left 20=ml20;Margin-Top 10=mt10;Margin-Top 20=mt20;',
			
			
			content_css:webdir+'css/main.css',
			body_class:'variable'
		};
		
		if(barType==='basic'){
			this.attrs.plugins="springbok,style,fullscreen,inlinepopups,contextmenu,advlink"+(withImageManager?',advimage,springbokgallery':'');
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,styleselect,fontsizeselect,,forecolor,|,bullist,numlist,|,link,unlink"+(withImageManager?',image,springbokAddImage':'')+',|,removeformat,visualaid';
			this.attrs.theme_advanced_buttons2=this.attrs.theme_advanced_buttons3="";
			//attrs.theme_advanced_buttons4="insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak";
		}else if(barType==='basicAdvanced'){
			this.attrs.plugins="springbok,style,fullscreen,inlinepopups,contextmenu,springboklink"+(withImageManager?',advimage,springbokgallery':'')+',springbokclean';
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,fontsizeselect,formatselect,|,bullist,numlist,sub,sup,|,link,unlink"+(withImageManager?',image,springbokAddImage':'')+',|,removeformat,visualaid';
			this.attrs.theme_advanced_buttons2=this.attrs.theme_advanced_buttons3="";
		}else{
			this.attrs.plugins="springbok,pagebreak,style,fullscreen,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste"+(withImageManager?',springbokgallery':'')+',springbokclean';
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect,|,cut,copy,paste,pastetext,pasteword,|,cleanup,help";
			this.attrs.theme_advanced_buttons2="bullist,numlist,|,sub,sup,|,link,unlink,anchor,image,"+(withImageManager?'springbokAddImage,':'')+"charmap,media,syntaxhl,|,forecolor,backcolor,|,search,replaceoutdent,indent,blockquote,|,hr,tablecontrols,|,undo,redo";
			this.attrs.theme_advanced_buttons3="";
		}
		
		this.load(this.attrs.plugins);
		
		/*        this.attrs.style_formats=[
                //{title : 'Bleu canard', inline:'span', classes:'special'},
                {title: 'Bleu canard',                inline:'span',styles:{color:'#00959e'}},
		*/
		
		return this;
	},
	wordCount:function(){ this.attrs.plugins+=',wordcount'; return this; },
	syntaxhl:function(){ this.attrs.plugins+=',syntaxhl'; return this; },
	autolink:function(){ this.attrs.plugins+=',autolink'; return this; },
	autoSave:function(){ this.attrs.plugins+=',autosave'; this.attrs.theme_advanced_buttons1+=",|,restoredraft"; return this; },
	simpleText:function(){
		this.attrs.force_br_newlines=true;
		this.attrs.force_p_newlines=false;
		this.attrs.forced_root_block='';
		return this;
	},
	
	absoluteUrls:function(withDomain){
		this.attrs.convert_urls=true;
		this.attrs.relative_urls=false;
		this.attrs.remove_script_host=withDomain?true:false;
		this.attrs.document_base_url="http://www.site.com/"+base_url;
	},
	
	addAttr:function(name,value){
		this.attrs[name]=value;
		return this;
	},

	forEmail:function(){
		S.extendsObj(this.attrs,{
			/* border=0 for img */
			extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]"
		});
		return this;
	},
	
	withAjaxFileManager:function(){
		this.attrs.file_browser_callback="ajaxfilemanager";
		return this;
	},
	
	onChange:function(callback){
		this.attrs.onchange_callback=callback;
		return this;
	},
	
	handleEvent:function(callback){
		this.attrs.handle_event_callback=callback;
		return this;
	},
	
	validXHTML:function(){
		this.attrs.entity_encoding='named';
		this.attrs.valid_elements =""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"abbr[class|dir<ltr?rtl|id|lang|le|title],"
+"acronym[class|dir<ltr?rtl|id|id|lang|le|title],"
+"address[class|align|dir<ltr?rtl|id|lang|le|title],"
+"area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref|shape<circle?default?poly?rect|style|tabindex|title|target],"
+"base[href|target],"
+"basefont[color|face|id|size],"
+"bdo[class|dir<ltr?rtl|id|lang|style|title],"
+"big[class|dir<ltr?rtl|id|lang|le|title],"
+"blockquote[cite|class|dir<ltr?rtl|id|lang|le|title],"
+"body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink],"
+"br[class|clear<all?left?none?right|id|style|title],"
+"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|style|tabindex|title|type|value],"
+"caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|le|title],"
+"center[class|dir<ltr?rtl|id|lang|le|title],"
+"cite[class|dir<ltr?rtl|id|lang|le|title],"
+"code[class|dir<ltr?rtl|id|lang|le|title],"
+"col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|span|style|title|valign<baseline?bottom?middle?top|width],"
+"colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|span|style|title|valign<baseline?bottom?middle?top|width],"
+"dd[class|dir<ltr?rtl|id|lang|le|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"dfn[class|dir<ltr?rtl|id|lang|le|title],"
+"dir[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"dt[class|dir<ltr?rtl|id|lang|le|title],"
+"em/i[class|dir<ltr?rtl|id|lang|le|title],"
+"fieldset[class|dir<ltr?rtl|id|lang|le|title],"
+"font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],"
+"form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang|method<get?post|name|onreset|onsubmit|style|title|target],"
+"frame[class|frameborder|id|longdesc|marginheight|marginwidth|name|noresize<noresize|scrolling<auto?no?yes|src|style|title],"
+"frameset[class|cols|id|onload|onunload|rows|style|title],"
+"h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"head[dir<ltr?rtl|lang|profile],"
+"hr[class|dir<ltr?rtl|id|lang|noshade<noshade|size|style|title|width],"
+"html[dir<ltr?rtl|lang|version],"
+"iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width],"
+"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height|hspace|id|ismap<ismap|lang|longdesc|name|src|style|title|usemap|vspace|width],"
+"input[accept|accesskey|align<bottom?left?middle?right?top|alt|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang|maxlength|name|onselect|readonly<readonly|size|src|style|tabindex|title|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text|usemap|value],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],"
+"kbd[class|dir<ltr?rtl|id|lang|le|title],"
+"label[accesskey|class|dir<ltr?rtl|for|id|lang|style|title],"
+"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang|le|title],"
+"li[class|dir<ltr?rtl|id|lang|le|title|type|value],"
+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|rel|rev|style|title|target|type],"
+"map[class|dir<ltr?rtl|id|lang|name|le|title],"
+"menu[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],"
+"noframes[class|dir<ltr?rtl|id|lang|le|title],"
+"noscript[class|dir<ltr?rtl|id|lang|style|title],"
+"object[align<bottom?left?middle?right?top|archive|border|class|classid|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name|standby|style|tabindex|title|type|usemap|vspace|width],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|le|title],"
+"option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|selected<selected|style|title|value],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"param[id|name|type|value|valuetype<DATA?OBJECT?REF],"
+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|le|title|width],"
+"q[cite|class|dir<ltr?rtl|id|lang|le|title],"
+"s[class|dir<ltr?rtl|id|lang|le|title],"
+"samp[class|dir<ltr?rtl|id|lang|le|title],"
+"script[charset|defer|language|src|type],"
+"select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name|size|style|tabindex|title],"
+"small[class|dir<ltr?rtl|id|lang|le|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"strike[class|class|dir<ltr?rtl|id|lang|le|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|le|title],"
+"style[dir<ltr?rtl|lang|media|title|type],"
+"sub[class|dir<ltr?rtl|id|lang|le|title],"
+"sup[class|dir<ltr?rtl|id|lang|le|title],"
+"table[bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|rules|style|summary|title|width],"
+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name|onselect|readonly<readonly|rows|style|tabindex|title],"
+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"title[dir<ltr?rtl|lang],"
+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"tt[class|dir<ltr?rtl|id|lang|le|title],"
+"u[class|dir<ltr?rtl|id|lang|le|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|le|title|type],"
+"var[class|dir<ltr?rtl|id|lang|le|title]";
		return this;
	},
	
	validSimpleHTML:function(){
		this.attrs.valid_elements=""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"br[class|id|style|title],"
+"dd[class|dir<ltr?rtl|id|lang|le|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"div[class|dir<ltr?rtl|id|lang|le|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"dt[class|dir<ltr?rtl|id|lang|le|title],"
+"hr[class|dir<ltr?rtl|id|lang|noshade<noshade|size|style|title|width],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"li[class|dir<ltr?rtl|id|lang|le|title|type|value],"
+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|rel|rev|style|title|target|type],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|le|title|width],"
+"small[class|dir<ltr?rtl|id|lang|le|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|le|title],"
+"sub[class|dir<ltr?rtl|id|lang|le|title],"
+"sup[class|dir<ltr?rtl|id|lang|le|title],"
+"table[bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|rules|style|summary|title|width],"
+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"u[class|dir<ltr?rtl|id|lang|le|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|le|title|type]";
		return this;
	},
	
	validBasicHTML:function(){
		this.attrs.valid_elements=""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"br[class|clear<all?left?none?right|id|style|title],"
+"dd[class|dir<ltr?rtl|id|lang|style|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
+"dt[class|dir<ltr?rtl|id|lang|style|title],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
+"li[class|dir<ltr?rtl|id|lang|style|title|type|value],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"small[class|dir<ltr?rtl|id|lang|style|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|style|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|style|title],"
+"sub[class|dir<ltr?rtl|id|lang|style|title],"
+"sup[class|dir<ltr?rtl|id|lang|style|title],"
+"u[class|dir<ltr?rtl|id|lang|style|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|style|title|type]";
		return this;
	},
	
	createForTextareas:function(){
		this.attrs.mode='textareas';
		return tinymce.init(this.attrs);
	},
	
	createForIds:function(ids){
		this.attrs.mode='exact';
		this.attrs.elements=ids;
		tinymce.init(this.attrs);
	},
	
	
	ajaxSave:function(name,url){
		var ed=tinymce.get(name);
		ed.setProgressState(1);
		$.post(url,{text:ed.getContent()},function(){ed.setProgressState(0)});
	},
	
	
	switchtoHtml:function(editorId){
		var ed=tinyMCE.get(editorId),dom=tinymce.DOM,txtarea_el = dom.get(editorId);
		//tinyMCE.execCommand('mceRemoveControl',false,1);
		if(!ed || ed.isHidden()) return false;
		txtarea_el.style.height = ed.getContentAreaContainer().offsetHeight+20+'px';
		ed.hide();
	},
	switchtoVisual:function(editorId){
		var ed=tinyMCE.get(editorId)/*,dom=tinymce.DOM,txtarea_el = dom.get(editorId)*/;
		//tinyMCE.execCommand('mceAddControl',false,1);
		if(!ed || !ed.isHidden()) return false;
		ed.show();
	}
};
