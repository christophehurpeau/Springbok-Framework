<?php
/** @deprecated */
class HTinymce{
	public static function link($settings){
		// from tiny_mce_gzip.php
		$scriptSrc = BASE_URL.'/web/tiny_mce/tiny_mce_gzip.php?js=1';
		
		// Add plugins
		if (isset($settings["plugins"]))
			$scriptSrc .= "&plugins=" . (is_array($settings["plugins"]) ? implode(',', $settings["plugins"]) : $settings["plugins"]);
		
		// Add themes
		if (isset($settings["themes"]))
			$scriptSrc .= "&themes=" . (is_array($settings["themes"]) ? implode(',', $settings["themes"]) : $settings["themes"]);

		// Add languages
		if (isset($settings["languages"]))
			$scriptSrc .= "&languages=" . (is_array($settings["languages"]) ? implode(',', $settings["languages"]) : $settings["languages"]);

		// Add disk_cache
		if (isset($settings["disk_cache"]))
			$scriptSrc .= "&diskcache=" . ($settings["disk_cache"] === true ? "true" : "false");
		
		// Add any explicitly specified files if the default settings have been overriden by the tag ones
		/*
		 * Specifying tag files will override (rather than merge with) any site-specific ones set in the 
		 * TinyMCE_Compressor object creation.  Note that since the parameter parser limits content to alphanumeric
		 * only base filenames can be specified.  The file extension is assumed to be ".js" and the directory is
		 * the TinyMCE root directory.  A typical use of this is to include a script which initiates the TinyMCE object. 
		 */
		if (isset($tagSettings["files"]))
			$scriptSrc .= "&files=" .(is_array($settings["files"]) ? implode(',', $settings["files"]) : $settings["files"]);

		// Add src flag
		if (isset($settings["source"]))
			$scriptSrc .= "&src=" . ($settings["source"] === true ? "true" : "false");
		
		return '<script type="text/javascript" src="'.h($scriptSrc).'"></script>';
	}
	
	public static function simpleValid($width,$height){
		ob_start(); ?>
$(document).ready(function(){tinyMCE.init({
	mode:"textareas", theme:"advanced", language:'fr',
	skin :"o2k7", skin_variant:"black",
	theme_advanced_toolbar_location:"top", theme_advanced_toolbar_align:"left", theme_advanced_statusbar_location:"bottom", theme_advanced_resizing:false,

	content_css:'<?php echo HHtml::staticUrl('/main.css','css') ?>',
	'width':'<?php echo $width ?>', 'height':'<?php echo $height ?>',

	invalid_elements : "strong,em,small",convert_fonts_to_spans:true,
	doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	apply_source_formatting:false,
	formats:{
		bold:{inline:'span', styles:{'font-weight':'bold'}},
		italic:{inline:'span', styles:{'font-style':'italic'}}
	},
	
	plugins:"autolink,fullscreen",
	theme_advanced_buttons1:"bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,fullscreen,code",
	theme_advanced_buttons2:"",
	theme_advanced_buttons3:"",
	
	entity_encoding:"named",
	valid_elements:""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"br[class|clear<all?left?none?right|id|style|title],"
+"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type|value],"
+"dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style|title|width],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type|value],"
+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|start|style|title|type],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|width],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules|style|summary|title|width],"
+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],"
+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],"
+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],"
+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|valign<baseline?bottom?middle?top],"
+"u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type],",

	init_instance_callback:function(ed){
		$(tinymce.DOM.get(ed.id +'_path_row')).before('<div style="margin-right:20px" id='+ed.id+'_count_words_and_char>0 Mots, 0 Caractères</div>');
	},
	setup:function(ed){
		ed.onKeyUp.add(function(ed, e){
			var strip = $$.html.entitiesDecode(tinyMCE.activeEditor.getContent().replace(/(<([^>]+)>)/g,''));
			var text = strip.split(' ').length + " Mots, " +  strip.length + " Caractères"
			tinymce.DOM.setHTML(tinymce.DOM.get(tinyMCE.activeEditor.id +'_count_words_and_char'),text);   
		});
	}
})}); <?php
		return HHtml::jsInline(ob_get_clean());
	}
}
