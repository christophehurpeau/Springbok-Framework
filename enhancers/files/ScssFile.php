<?php
/* http://code.google.com/p/phamlp/ */
include CLIBS.'phamlp'.DS.'sass'.DS.'SassParser.php';
class ScssFile extends EnhancerFile{
	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){
		$sass = new SassParser(array('cache'=>false,));
		return $sass->toCss($this->getPath());
	}
	
	public function getEnhancedProdContent(){
		$sass = new SassParser(array('cache'=>false,'style'=>'compressed'));
		return $sass->toCss($this->getPath());
		
		// Strip // comments
		$content=preg_replace('/\/\/(.*)?\n/','',$content);
		// Strip /* */ comments
		$content=preg_replace('/\/\*[\s\S]*?\*\//m','',$content);

		$content=preg_replace('/\s*}/m','}',$content);
		$content=preg_replace('/;\s*/m',';',$content);
		$content=preg_replace('/\s*([{|,])\s*/m','$1',$content);
		$content=preg_replace_callback('/\s*{(.*)}\s*/m',function($matches){
			return '{'.preg_replace('/\s*:\s*/m',':',$matches[1]).'}';
		},$content);
////                $content=preg_replace('/\s*({.*)\s*:\s*(.*})\s*/m','$1:$2',$content);

		// reduce non-newline whitespace to one
		$content=preg_replace('/[ \f]+/',' ',$content);
		// newlines (preceded by any whitespace) to a whitespace (rare)
		$content=preg_replace('/\s*\n+/m',' ',$content);
		$content=str_replace(';}','}',$content);

		$content=str_replace(':white;',':#FFF;',$content);
		$content=str_replace('border:none','border:0',$content);
//		$content=preg_replace('/([ |:]0)[px|pt|em]/im','$1',$content);

		$content=$this->removeWS_B_E($content);
		
		return $content;
	}
}
