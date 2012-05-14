<?php
class HText{
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
			default: $content=h($content); break;
		}
		
		$content=explode("\n",$content);
		if($withLineNumbers) $withLineNumbers='%'.strlen((string)count($content)).'d';
		
		if(($ok=($line <= count($content)))){
			$start=array_slice($content,$firstline=max(0,($minmax?($line-1-$minmax):0)),$line-1-$firstline);
			$lineContent=$line==0?'':$content[$line-1];
			$end=$minmax?array_slice($content,$line,$minmax):array_slice($content,$line);
		}else $start=$content;
		
		$content=self::lines($withLineNumbers,$ok?$firstline+1:1,$start,$formatter);
		if($ok){
			$attributes=array();
			if($class) $attributes['class']=$class;
			if($style) $attributes['style']=$style;
			$content.=self::line($withLineNumbers,$line,$attributes,$lineContent,$formatter);
			$content.=self::lines($withLineNumbers,$line+1,$end,$formatter);
		}
		return HHtml::tag('pre',$preAttrs,$content,false);
	}
	
	private static function &lines($withLineNumbers,$startNumLine,$lines){
		$content='';
		foreach($lines as &$line) $content.=self::line($withLineNumbers,$startNumLine++,array(),$line);
		return $content;
	}
	
	private static function line($withLineNumbers,$numLine,$attributes,$contentLine){
		return HHtml::tag('div',$attributes,($withLineNumbers?('<i style="color:#AAA;font-size:7pt;">'.sprintf($withLineNumbers,$numLine).'</i> '):'').$contentLine,false);
	}
}