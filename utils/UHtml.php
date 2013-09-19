<?php
/** HTML Utils */
class UHtml{
	/**
	 * Transform internal links into real links
	 * 
	 * <code>
	 * $post->text=UHtml::transformInternalLinks($post->text,array(
	 * 	'article'=>function($id){$postSlug=Post::findValueSlugById($id); return array('/:controller/:id-:slug','posts',sprintf('%03d',$id),$postSlug);}
	 * ))
	 * </code>
	*/
	public static function transformInternalLinks($content,$routes,$entryUrls='index',$fullUrls=null){
		return preg_replace_callback('#<a([^>]+data\-role="internalLink"[^>]*)>#U',function($m) use($routes,$entryUrls,$fullUrls){
			preg_match('/data\-type="([^"]+)"/',$m[1],$type);
			preg_match('/data\-params="([^"]+)"/',$m[1],$params);
			
			$internalLink=call_user_func_array(array($routes[$type[1]],'internalLink'),
					json_decode(html_entity_decode($params[1],ENT_QUOTES,'UTF-8'),true));
			if($internalLink===false)
				return '<a '.trim(preg_replace('#\s*(?:data\-role|data\-type|data\-params)="[^"]+"\s*#U',' ',$m[1])).'>';
			return '<a href="'.HHtml::urlEscape($internalLink,$entryUrls,$fullUrls).'" '
				.trim(preg_replace('#\s*(?:data\-role|data\-type|data\-params|href)="[^"]+"\s*#U',' ',$m[1])).'>';
		},$content);
	}
	
	
	/**
	 * Clean html like useless spaces or empty spans or doubled spans
	 * 
	 * @param string
	 * @return string
	 */
	public static function clean($html){
		// <([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>
		
		$cleanedHtml=str_replace('</p>',"</p>\n",trim($html)); // pour éviter des \n au milieu des balises
		$cleanedHtml=preg_replace_callback('/style="([^"]+)"/m',function($style){return 'style="'.preg_replace('/\s*([;|:])\s*/','$1',$style[1]).'"';},$cleanedHtml);
		$i=4;
		while($i-->0) $cleanedHtml=preg_replace('/<span[^>]*>\s*<\/span>/mU','',$cleanedHtml);
		$i=4;
		while($i-->0) $cleanedHtml=preg_replace_callback('/<span([^>]*)>(\s*)<span([^>]*)>(.*)<\/span>(\s*)<\/span>/mU',function($matches){
			$attributes=array();
			foreach(array($matches[1],$matches[3]) as $attrs){
				$attrsMatches=array();
				if(preg_match_all('/([a-zA-Z]+)="([^"]+)"/',$attrs,$attrsMatches) && $attrsMatches){
					foreach($attrsMatches[1] as $key=>&$attrName){
						if($attrName=='style'){
							$style=rtrim(trim($attrsMatches[2][$key]),';');
							isset($attributes[$attrName]) ? $attributes[$attrName].=';'.$style : $attributes[$attrName]=$style;
						}else $attributes[$attrName]=$attrsMatches[2][$key];
					}
				}
			}
			$res='';
			foreach($attributes as $k=>&$v) $res.=' '.$k.'="'.$v.'"';
			return '<span'.$res.'>'.$matches[2].$matches[4].$matches[5].'</span>';
		},$cleanedHtml);
	}
	
	
	
	/* http://snippets.dzone.com/posts/show/1964 */
	
	public static function clean_html($uncleanhtml,$indent='    '){
		//Uses previous function to seperate tags
		$fixed_uncleanhtml = self::fix_newlines_for_clean_html($uncleanhtml);
		$uncleanhtml_array = explode("\n", $fixed_uncleanhtml);
		//Sets no indentation
		$indentlevel = 0;
		foreach ($uncleanhtml_array as $uncleanhtml_key => $currentuncleanhtml)
		{
			//Removes all indentation
			$currentuncleanhtml = preg_replace("/\t+/",'', $currentuncleanhtml);
			$currentuncleanhtml = preg_replace("/^\s+/",'', $currentuncleanhtml);
			
			$replaceindent = "";
			
			//Sets the indentation from current indentlevel
			for ($o = 0; $o < $indentlevel; $o++)
				$replaceindent .= $indent;
			
			//If self-closing tag, simply apply indent
			if (preg_match("/<(.+)\/>/", $currentuncleanhtml))
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			//If doctype declaration, simply apply indent
			else if (preg_match("/<!(.*)>/", $currentuncleanhtml))
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			//If opening AND closing tag on same line, simply apply indent
			else if (preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && preg_match("/<\/(.*)>/", $currentuncleanhtml))
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			//If closing HTML tag or closing JavaScript clams, decrease indentation and then apply the new level
			else if (preg_match("/<\/(.*)>/", $currentuncleanhtml) || preg_match("/^(\s|\t)*\}{1}(\s|\t)*$/", $currentuncleanhtml)){
				$indentlevel--;
				$replaceindent = "";
				for ($o = 0; $o < $indentlevel; $o++)
					$replaceindent .= $indent;
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			}
			//If opening HTML tag AND not a stand-alone tag, or opening JavaScript clams, increase indentation and then apply new level
			else if ((preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && !preg_match("/<(link|meta|base|br|img|hr)(.*)>/", $currentuncleanhtml)) || preg_match("/^(\s|\t)*\{{1}(\s|\t)*$/", $currentuncleanhtml)){
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
				
				$indentlevel++;
				$replaceindent = "";
				for ($o = 0; $o < $indentlevel; $o++){
					$replaceindent .= $indent;
				}
			}else //Else, only apply indentation
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
		}
		//Return single string seperated by newline
		return implode("\n", $cleanhtml_array);	
	}
	
	//Function to seperate multiple tags one line
	private static function fix_newlines_for_clean_html($fixthistext){
		$fixthistext_array = explode("\n", $fixthistext);
		foreach ($fixthistext_array as $unfixedtextkey => $unfixedtextvalue){
			//Makes sure empty lines are ignores
			if (!preg_match("/^(\s)*$/", $unfixedtextvalue)){
				$fixedtextvalue = preg_replace("/>(\s|\t)*</U", ">\n<", $unfixedtextvalue);
				$fixedtext_array[$unfixedtextkey] = $fixedtextvalue;
			}
		}
		return implode("\n", $fixedtext_array);
	}
}
