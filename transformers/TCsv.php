<?php
/**
 * CSV Transformer
 */
class TCsv extends STransformer{
	/**
	 * Return the content type: text/csv
	 * 
	 * @return string
	 */
	public static function getContentType(){
		return 'text/csv';
	}
	
	private $content='';
	
	public function __construct(){
		ob_start();
	}
	
	public function titles($fields){
		foreach($fields as &$field){
			//if(isset($field['align'])) $th=' class="'.self::$tAligns[$field['align']].'"';
			//if(isset($field['widthPx'])) $th.=' style="width:'.$field['widthPx'].'px"';
			//elseif(isset($field['width%'])) $th.=' style="width:'.$field['width'].'%"';
			//echo '<th'.$th.'>'.h($field['title']).'</th>';
			$this->content.='"'.str_replace('"','\\"',UEncoding::fromUtf8($field['title'])).'";';
		}
		$this->content=substr($this->content,0,-1)."\n";
	}
	
	public function row($row,$fields){
		foreach($fields as $i=>&$field){
			$value=self::getValueFromModel($row,$field,$i);
			$value=$this->getDisplayableValue($field,$value,$row);
			$this->content.='"'.str_replace('"','\\"',is_string($value) ? UEncoding::fromUtf8($value) : $value).'";';
		}
		$this->content=substr($this->content,0,-1)."\n";
	}
	
	public function toFile($fileName){
		file_put_contents($fileName,$this->content);
	}
	
	public function display(){
		echo $this->content;
	}
}