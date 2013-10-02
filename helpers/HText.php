<?php
/**
 * Text Helper
 */
class HText{
	/**
	 * Hightlight a line and colorify some keywords (basic syntax hightlight)
	 * 
	 * @param string
	 * @param string|null php|js|yml
	 * @param int hightlighted line number or 0
	 * @param string|false
	 * @param string|false
	 * @param bool
	 * @param int|false
	 * @param array
	 * @return string <pre> with content of the file
	 */
	public static function highlightLine($content,$formatter=NULL,$line=0,$class='hightlight',$style=false,$withLineNumbers=false,$minmax=false,$preAttrs=array()){
		switch ($formatter) {
			//case 'php': $content=str_replace('<br/>',"\n",highlight_string($content,true)); break;
			case 'php':
				$content=h($content);
				$content=preg_replace('/\b(abstract|and|as|break|case|catch|class|clone|const|continue|declare|default|do|'
					.'elseif|else|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|final|foreach|for|function|global|goto|if|implements|interface|instanceof|namespace|'
					.'new|or|private|protected|public|static|switch|throw|try|use|var|while|xor)\b/im',
					'<b style="color:#CC0033">$1</b>',$content);
				$content=preg_replace('/\b(__CLASS__|__DIR__|__FILE__|__LINE__|__FUNCTION__|__METHOD__|__NAMESPACE__)\b/im',
					'<b style="color:#F42">$1</b>',$content);
				$content=preg_replace('/\b(<\?php|\?>|die|echo|empty|exit|eval|include|include_once|isset|list|require|require_once|return|print|unset)\b/im',
					'<b style="color:#606">$1</b>',$content);
				$content=preg_replace('/\$([A-Za-z0-9\_]+)\b/im','<span style="color:#33A"><b>\$</b>$1</span>',$content);
				$content=preg_replace('/\bself::/im','<i style="color:#33A">self</i>::',$content);
				//array()
				break;
			case 'js':
				$content=h($content);
				// https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Reserved_Words
				$content=preg_replace('/\b(break|case|catch|continue|debugger|default|delete|do|else|'
					.'finally|for|function|if|in|instanceof|new|return|switch|'
					.'this|throw|try|typeof|var|void|while|with|'
					.'class|enum|export|extends|import|super|'
					.'implements|interface|let|package|private|protected|public|static|yield|'
					.'const)\b/im','<b style="color:#CC0033">$1</b>',$content);
				$content=preg_replace('/\b(true|false|null|undefined)\b/im',
					'<b style="color:#606">$1</b>',$content);
				break;
			case 'yml':
				$content=h($content);
				$content=preg_replace('/^(\s*)(\'[\']*\'|\"[\"]*\"|[^:]+)\:/m','$1<i style="color:#33A">$2</i>:',$content);
				break;
			default: $content=h($content); break;
		}
		
		$content=explode("\n",$content);
		
		if(($ok=($line <= count($content)))){
			$start=array_slice($content,$firstline=max(0,($minmax?($line-1-$minmax):0)),$line-1-$firstline);
			$lineContent=$line==0?'':$content[$line-1];
			$end=$minmax?array_slice($content,$line,$minmax):array_slice($content,$line);
		}else $start=$content;
		
		if($withLineNumbers) $withLineNumbers='%'.strlen((string)($ok?$line+$minmax:$minmax+1)).'d';
		$content=self::lines($withLineNumbers,$ok?$firstline+1:1,$start,$formatter);
		if($ok){
			$attributes=array();
			if($class) $attributes['class']=$class;
			if($style) $attributes['style']=$style;
			$content.=self::line($withLineNumbers,$line,$attributes,$lineContent,$formatter);
			$content.=self::lines($withLineNumbers,$line+1,$end,$formatter);
		}
		!isset($preAttrs['style']) ? $preAttrs['style']='background:#FFF;color:#222;border:0;position:relative;'
					 : $preAttrs['style']=';background:#FFF;color:#222;border:0;position:relative;'.$preAttrs['style'];
		return HHtml::tag('pre',$preAttrs,$content,false);
	}
	
	/**
	 * @param bool
	 * @param int
	 * @param array
	 * @return string
	 */
	private static function lines($withLineNumbers,$startNumLine,$lines){
		$content='';
		foreach($lines as &$line) $content.=self::line($withLineNumbers,$startNumLine++,array(),$line);
		return $content;
	}
	
	/**
	 * @param bool
	 * @param int
	 * @param array
	 * @param string
	 * @return string
	 */
	private static function line($withLineNumbers,$numLine,$attributes,$contentLine){
		//!isset($attributes['style']) ? $attributes['style']='overflow:auto;' : $attributes['style'].=';overflow:auto;';
		!isset($attributes['style']) ? $attributes['style']='white-space:pre-wrap;'.($withLineNumbers?'padding-left:20px;':'')
										 : $attributes['style'].=';white-space:pre-wrap;'.($withLineNumbers?'padding-left:20px;':'');
		
		return HHtml::tag('div',$attributes,($withLineNumbers?('<i style="color:#AAA;font-size:7pt;position:absolute;left:1px;padding-top:1px;">'
					.sprintf($withLineNumbers,$numLine).'</i> '):'').$contentLine,false);
	}
}