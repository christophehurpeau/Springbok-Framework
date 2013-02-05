<?php
class TestNavigator extends CHttpClient{
	public function get($url,$entry='index'){
		try{ return parent::get($this->_url($url,$entry)); }catch(HttpClientError $e){}
	}
	public function getReal($url,$entry='index'){
		return $this->get($url[0]==='/' ? '\\'.$url : $url,$entry);
	}
	public function post($url,$entry='index'){
		return parent::post($this->_url($url,$entry));
	}
	public function ajaxGet($url,$entry='index'){
		return parent::ajaxGet($this->_url($url,$entry));
	}
	public function ajaxPost($url,$entry='index'){
		return parent::ajaxPost($this->_url($url,$entry));
	}
	private function _url($url,$entry){
		$url=HHtml::url($url,$entry,true);
		return $url.(strpos($url,'?')===false?'?':'&').'springbokNoEnhance=true&springbokNoDevBar=true';
	}
	
	public function assertStatus200(){
		assert($this->getStatus()===200);
	}
	
	protected function _beforeCurlCreate(){
		$this->parsedHtml=null;
	}
	
	private $parsedHtml;
	public function parseHtml(){
		include_once CLIBS.'simple_html_dom.php';
		return $this->parsedHtml=str_get_html($this->getResult());
	}
	private function _parseHtml(){
		if($this->parsedHtml===null) return $this->parseHtml();
		return $this->parsedHtml;
	}
	
	public function checkHeadLinks(){
		$parsedHtml=$this->_parseHtml();
		$links=$parsedHtml->find('head link');
		foreach($links as $link){
			$this->getReal($link->href);
			$this->assertStatus200();
		}
		$this->parsedHtml=$parsedHtml;
	}
}

class STest{
	public function _before(){}
	public function _after(){}
	public function launchTests(){
		$results=array();
		$tests=array_diff(get_class_methods(get_called_class()),array(),get_class_methods('STest'));
		foreach($tests as $testMethod){
			if($testMethod[0]==='_') continue;
			$result='ok';
			try{
				$this->$testMethod();
			}catch(Exception $e){
				$result=array('exception'=>$e);
			}
			$results[$testMethod]=$result;
		}
		return $results;
	}
	
	public function navigator(){
		return new TestNavigator;
	}
	
	public static function run(){
		$o=new static;
		return $o->launchTests();
	}
	
	public static function display($results){
		if($results===1) echo '<div class="message error">missing return for '+h($file)+' </div>';
		else{
			foreach($results as $fName => $result){
				echo '<div class="message '.($result==='ok'?'success':'error').'">'
						.'<b>'.h($fName).'</b> ';
				if(is_string($result)) echo $result;
				elseif(!empty($result['exception'])){
					echo '<u>Exception:</u> '.$result['exception']->getMessage();
					echo prettyHtmlBackTrace(0,$result['exception']->getTrace());
				}else echo UVarDump::dump($result,4,true);
				echo '</div>';
			}
		}
	}
}