<?php
class TestNavigator extends CHttpClient{
	private $testClass;
	public function __construct($testClass){
		$this->testClass=$testClass;
		$this->doNotFollowRedirects();
		$this->parseHeaders();
	}
	
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
	
	public function foreachIndexAndMobile($callback){
		foreach(array('index','mobile') as $entry){
			$entry==='mobile' ? CHttpClient::userAgentIphone() : CHttpClient::userAgentDefault();
			$callback($this,$entry);
		}
	}
	
	public function status200(){
		if($this->getStatus()!==200)
			throw new Exception($this->getLastUrl().' : '.$this->getStatus());
	}
	
	public function checkRedirectPermanent($to,$index=null){
		if($this->getStatus()!==301)
			throw new Exception($this->getLastUrl().' : '.$this->getStatus());
		$this->equals($this->getHeader('location'),($index===null?'':App::siteUrl($index,false)).$to);
	}
	
	protected function _beforeCurlCreate(){
		$this->parsedHtml=null;
	}
	
	private $parsedHtml,$currentParsedUrl,$metas,$h1;
	public function parseHtml(){
		include_once CLIBS.'simple_html_dom.php';
		$this->currentParsedUrl=$this->getLastUrl();
		$this->metas=null;
		return $this->parsedHtml=str_get_html($this->getResult());
	}
	private function _parseHtml(){
		if($this->parsedHtml===null) return $this->parseHtml();
		return $this->parsedHtml;
	}
	
	public function html200(){
		$this->status200();
		return $this->checkHtml();
	}
	
	public function checkHtml(){
		$this->checkHeadLinks();
		$this->metas=$this->checkMetas();
		$parsedHtml=$this->_parseHtml();
		$h1=$parsedHtml->find('body h1');
		$this->check($h1,'<h1>')->size(1);
		$this->h1=$h1[0];
		$this->check($this->h1->innertext,'<h1>')->doubleSpace();
		return $this->parsedHtml;
	}
	
	public function checkH1($text){
		return $this->check($this->h1->innertext,'<h1>')->equals($text);
	}
	
	public function checkHeadLinks(){
		$parsedHtml=$this->_parseHtml();
		$links=$parsedHtml->find('head link');
		foreach($links as $link){
			$this->getReal($link->href);
			$this->status200();
		}
		$this->parsedHtml=$parsedHtml;
	}
	public function checkMetas(){
		if($this->metas!==null) return $this->metas;
		$parsedHtml=$this->_parseHtml();
		
		// http://www.sagerock.com/blog/title-tag-meta-description-length/
		
		$metaTitle=$parsedHtml->find('head title');
		$this->check($metaTitle,'Meta title tags')->size(1);
		$metaTitle=$metaTitle[0]; $metaTitleText=hdecode($metaTitle->innertext);
		$c=$this->check($metaTitleText,'Meta title')->doubleSpace()->minLength(25);
		if($this->testClass->_mustBePerfect()) $c->maxLength(69);
		
		$metaDescription=$parsedHtml->find('head meta[name="description"]');
		$this->check($metaDescription,'Meta description tags')->size(1);
		$metaDescription=$metaDescription[0]; $metaDescriptionContent=hdecode($metaDescription->content);
		$c=$this->check($metaDescriptionContent,'Meta description')->doubleSpace()->minLength(50);
		if($this->testClass->_mustBePerfect()) $c->maxLength(150);
		
		$metaKeywords=$parsedHtml->find('head meta[name="keywords"]');
		$this->check($metaKeywords,'Meta keywords tags')->size(1);
		$metaKeywords=$metaKeywords[0]; $metaKeywordsContent=hdecode($metaKeywords->content);
		$this->check($metaKeywordsContent,'Meta keywords')->doubleSpace();
		
		$metaOgSiteName=$parsedHtml->find('head meta[property="og:site_name"]');
		$this->check($metaOgSiteName,'Meta og:site_name tags')->size(1);
		$metaOgSiteName=$metaOgSiteName[0]; $metaOgSiteNameContent=hdecode($metaOgSiteName->content);
		$this->check($metaOgSiteNameContent,'Meta og:site_name')->doubleSpace();
		
		$metaOgTitle=$parsedHtml->find('head meta[property="og:title"]');
		$this->check($metaOgTitle,'Meta og:title tags')->size(1);
		$metaOgTitle=$metaOgTitle[0]; $metaOgTitleContent=hdecode($metaOgTitle->content);
		$this->check($metaOgTitleContent,'Meta og:title')->doubleSpace();
		
		
		return array('metaTitle'=>$metaTitle,'title'=>$metaTitleText,
				'metaDescription'=>$metaDescription,'description'=>$metaDescriptionContent,
				'metaKeywords'=>$metaKeywords,'keywords'=>$metaKeywordsContent,
				'meta_og_siteName'=>$metaOgSiteName, 'og_siteName'=>$metaOgSiteNameContent,
				'meta_og_title'=>$metaOgTitle, 'og_title'=>$metaOgTitleContent
		);
	}
	
	public function __call($method,$args){
		return call_user_func_array(array($this->testClass,$method),$args);
	}
}

class STest{
	public static $testEnv=false,$perfect=true;
	
	private $lastNavigator;
	
	public function _mustBePerfect(){ return static::$perfect; }
	
	public function _before(){}
	public function _after(){}
	public function launchTests(){
		if(static::$testEnv) return array('env'=>array('exception'=>new Exception('not yet implemented')));
		$results=array();
		$tests=array_diff(get_class_methods(get_called_class()),array(),get_class_methods('STest'));
		foreach($tests as $testMethod){
			if($testMethod[0]==='_') continue;
			$result='pass';
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
		return $this->lastNavigator=new TestNavigator($this);
	}
	
	public static function run(){
		$o=new static;
		return $o->launchTests();
	}
	
	public static function runFile($filePath){
		$results=include $filePath;
		if(empty($results) || $results===1){
			$className=basename($filePath,'.php').'Test';
			if(class_exists($className,false)){
				$results=$className::run();
			}
		}
		return $results;
	}

	public static function directoryIterator(){
		return new RecursiveDirectoryIterator(APP.'tests',FilesystemIterator::SKIP_DOTS);
	}
	
	public static function display($results){
		if($results===1) echo '<div class="message error">missing return</div>';
		else{
			foreach($results as $fName => $result){
				echo '<div class="message '.($result==='pass'?'success':'error').'">'
						.'<b class="methodName">'.h($fName).'</b> ';
				if(is_string($result)) echo $result;
				elseif(!empty($result['exception'])){
					echo '<u>Exception:</u> ';
					if($result['exception'] instanceof SDetailedException){
						echo $result['exception']->toHtml();
					}else{
						echo $result['exception']->getMessage();
					}
					echo '<div class="italic">in '.$result['exception']->getFile().':'.$result['exception']->getLine().'</div>';
					echo prettyHtmlBackTrace(0,$result['exception']->getTrace());
				}else echo UVarDump::dump($result,4,true);
				echo '</div>';
			}
		}
	}
	
	public static function cliDisplay($results){
		$nbResults=$resultsFailed=0;
		if($results===1) echo cliColor('missing return',CliColors::red)."\n";
		else{
			foreach($results as $fName => $result){
				$nbResults++;
				if($result==='pass') echo '✔ '.$fName."\n";
				else{
					$resultsFailed++;
					echo cliColor('✖ ',CliColors::red).$fName."\n";
					if(is_string($result)) echo $result;
					elseif(!empty($result['exception'])){
						echo cliColor('Exception:',CliColors::red).' '.$result['exception']->getMessage()."\n";
						echo 'in '.$result['exception']->getFile().':'.$result['exception']->getLine();
						echo prettyBackTrace(0,$result['exception']->getTrace());
					}else echo UVarDump::dump($result,4,false);
				}
			}
		}
		return array('total'=>$nbResults,'failed'=>$resultsFailed);
	}
	
	public function check($var,$varInfo=null){
		return new STestCheck($this,$var,$varInfo);
	}
	
	public function __call($method,$args){
		return call_user_func_array(array(new STestCheck($this,array_shift($args)),$method),$args);
	}
	
	public function ex($message,$details){
		throw new SDetailedException($message,0,null,$details.(empty($this->lastNavigator)?'':"\n".'Last URL='.$this->lastNavigator->getLastUrl().' ['.$this->lastNavigator->getStatus().']'));
	}
}

class STestCheck{
	private $testClass,$var,$varInfo,$length;
	
	public function __construct($testClass,$var,$varInfo=null){
		$this->testClass=$testClass;
		$this->var=$var;
		$this->varInfo=$varInfo;
	}
	
	private function _varInfoOr($or){
		return $this->varInfo===null?$or:$this->varInfo;
	}
	
	private function ex($message,$details){
		$this->testClass->ex($message,$details);
	}
	
	/* ALL */
	
	public function equals($expected){
		if($this->var!==$expected)
			throw new Exception('"'.$this->var.'" !== "'.$expected.'"');
		return $this;
	}
	
	/* ARRAY */
	
	public function isArray(){
		if(!is_array($this->var))
			$this->ex($this->_varInfoOr('The var').' is not an array','type='.gettype($this->var).', var= '.$this->var);
		return $this;
	}
	public function _getCount(){
		$this->isArray();
		if($this->length===null) return $this->length=count($this->var);
		return $this->length;
	}
	
	public function size($size){
		if($this->_getCount()!==$size)
			$this->ex($this->_varInfoOr('The array').' has a size of '.$this->_getCount().', not '.$size,'array= '.UVarDump::dump($this->var,3,false));
		
	}
	
	/* ARRAY OR STRING */
	
	public function contains($string){
		if(is_string($this->var)){
			if(UString::pos($this->var,$string)===false)
				$this->ex($this->_varInfoOr('The string').' does not contains "'.$string.'"','string= '.$this->var);
		}elseif( is_array($this->var) ){
			if(array_key_exists($string,$this->var)===false)
				$this->ex($this->_varInfoOr('The array').' does not contains the key "'.$string.'"','array= '.UVarDump::dump($this->var,3,false));
		}else
			$this->ex($this->_varInfoOr('This').' is not a string nor an array','val= '.UVarDump::dump($this->var,3,false));
	}
	
	
	/* STRING */
	
	public function isString(){
		if(!is_string($this->var))
			$this->ex($this->_varInfoOr('The var').' is not a string','type='.gettype($this->var).', var= '.UVarDump::dump($this->var,3,false));
		if(($enc=mb_detect_encoding($this->var,'UTF-8, ISO-8859-15, ASCII, GBK'))!=='UTF-8')
			$this->ex($this->_varInfoOr('The string').' is not an UTF-8 string','encoding='.($enc?$enc:'unknown').', string= '.iconv($enc,'UTF-8',$this->var));
		return $this;
	}
	public function _getLength(){
		$this->isString();
		if($this->length===null) return $this->length=UString::length($this->var);
		return $this->length;
	}
	
	public function maxLength($maxLength){
		$l=$this->_getLength();
		$l=strlen($this->var);
		if($l>$maxLength)
			$this->ex('The length of '.$this->_varInfoOr('the string').' is > '.$maxLength,'size= '.$l.', string= '.$this->var);
		return $this;
	}
	public function minLength($minLength){
		$l=$this->_getLength();
		if($l<$minLength)
			$this->ex('The length of '.$this->_varInfoOr('the string').' is < '.$minLength,'size= '.$l.', string= '.$this->var);
		return $this;
	}

	public function doubleSpace(){
		$this->isString();
		/*if(strpos($this->var,'  ')!==false)*/
		if(preg_match('/\h{2,}/u',$this->var))
			$this->ex($this->_varInfoOr('The string').' has at least two successive spaces','string= '.preg_replace('/\h/u','[space]',$this->var));
		return $this;
	}
	
}
