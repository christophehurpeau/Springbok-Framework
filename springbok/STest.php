<?php
/**
 * Test Navigator
 */
class TestNavigator extends CHttpClient{
	private $testClass,$currentUrl,$defaultEntry='index';
	
	/**
	 * @param STest
	 */
	public function __construct($testClass){
		$this->testClass=$testClass;
		$this->doNotFollowRedirects();
		$this->parseHeaders();
	}
	
	/**
	 * Set the default entry for requests.
	 *
	 * @return TestNavigator
	 */
	public function setDefaultEntry($defaultEntry){
		$this->defaultEntry = $defaultEntry;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getCurrentUrl(){
		return $this->currentUrl;
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param bool
	 */
	public function get($url,$entry=null,$_internal=false){
		try{ return parent::get($this->_url($url,$entry,$_internal)); }catch(HttpClientError $e){}
	}
	
	/**
	 * @param string
	 * @param string
	 * @param bool
	 */
	public function getReal($url,$entry=null,$_internal=false){
		$url=$url[0]==='/' ? '\\'.$url : $url;
		if($_internal===false) $this->currentUrl=$url;
		return $this->get($url,$entry);
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param bool
	 */
	public function post($url,$entry=null,$_internal=false){
		try{ return parent::post($this->_url($url,$entry,$_internal)); }catch(HttpClientError $e){}
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param bool
	 */
	public function ajaxGet($url,$entry=null,$_internal=false){
		try{ return parent::ajaxGet($this->_url($url,$entry,$_internal)); }catch(HttpClientError $e){}
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param bool
	 */
	public function ajaxPost($url,$entry=null,$_internal=false){
		try{ return parent::ajaxPost($this->_url($url,$entry,$_internal)); }catch(HttpClientError $e){}
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param bool
	 */
	private function _url($url,$entry,$_internal){
		$url=HHtml::url($url,$entry !== null ? $entry : $this->defaultEntry,true,false,false,true);
		if($_internal===false) $this->currentUrl=$url;
		return $url.(strpos($url,'?')===false?'?':'&').'springbokNoEnhance=true&springbokNoDevBar=true';
	}
	
	/**
	 * Execute the callback test for both index and mobile entries
	 * 
	 * @param function function($test,$entry)
	 * @return void
	 */
	public function foreachIndexAndMobile($callback){
		foreach(array('index','mobile') as $entry){
			$entry==='mobile' ? CHttpClient::userAgentIphone() : CHttpClient::userAgentDefault();
			$callback($this,$entry);
		}
	}
	
	/**
	 * Checks if the Http Code is 200
	 * 
	 * @return void
	 */
	public function status200(){
		if($this->getStatus()!==200)
			$this->testClass->ex('Status: '.$this->getStatus().' !== 200',
						$this->getStatus()===301||$this->getStatus()===302?' to '.$this->getHeader('location'):'');
	}
	
	/**
	 * Checks if the Http Code is 301
	 * 
	 * @param string
	 * @param string|null
	 * @return void
	 */
	public function checkRedirectPermanent($to,$index=null){
		if($this->getStatus()!==301)
			$this->testClass->ex('Status: '.$this->getStatus().' !== 301','');
		$this->equals($this->getHeader('location'),($index===null?'':App::siteUrl($index,false)).$to);
	}
	
	/**
	 * Checks if the Http Code is 302
	 * 
	 * @param string
	 * @param string|null
	 * @return void
	 */
	public function checkRedirect($to,$index=null){
		if($this->getStatus()!==302)
			$this->testClass->ex('Status: '.$this->getStatus().' !== 302','');
		$this->equals($this->getHeader('location'),($index===null?'':App::siteUrl($index,false)).$to);
	}
	
	protected function _beforeCurlCreate(){
		$this->parsedHtml=null;
	}
	
	private $parsedHtml,$metas,$h1;
	/**
	 * @return simple_html_dom
	 */
	public function parseHtml(){
		include_once CORE.'libs/simple_html_dom.php';
		$this->metas=null;
		return $this->parsedHtml=str_get_html($this->getResult());
	}
	/**
	 * @return simple_html_dom
	 */
	private function _parseHtml(){
		if($this->parsedHtml===null) return $this->parseHtml();
		return $this->parsedHtml;
	}
	
	/**
	 * Checks if the Http Code is 200, then check the Html Page
	 * 
	 * @return simple_html_dom
	 * @see checkHtml
	 */
	public function html200(){
		$this->status200();
		return $this->checkHtml();
	}
	
	/**
	 * Checks the HTML page
	 * 
	 * @see checkHeadLinks
	 * @see checkMetas
	 * @return simple_html_dom
	 */
	public function checkHtml(){
		$this->checkHeadLinks();
		$this->metas=$this->checkMetas();
		$parsedHtml=$this->_parseHtml();
		if(empty($parsedHtml)) $this->testClass->ex('Not Valid Html','');
		$h1=$parsedHtml->find('body h1');
		$this->check($h1,'<h1>')->size(1);
		$this->h1=$h1[0];
		$this->check($this->h1->innertext,'<h1>')->doubleSpace();
		return $this->parsedHtml;
	}
	
	/**
	 * Check if the h1 equals to the text
	 * 
	 * @param string
	 * @return STestCheck
	 */
	public function checkH1($text){
		return $this->check($this->h1->innertext,'<h1>')->equals($text);
	}
	
	/**
	 * Check if all links in the <head> tag return an 200 Http code
	 * 
	 * @return void
	 */
	public function checkHeadLinks(){
		$parsedHtml=$this->_parseHtml();
		$links=$parsedHtml->find('head link');
		foreach($links as $link){
			$this->getReal($link->href,null,true);
			$this->status200();
		}
		$this->parsedHtml=$parsedHtml;
	}
	
	/**
	 * Checks the meta of the page
	 * 
	 * @return array list of metas
	 */
	public function checkMetas(){
		if($this->metas!==null) return $this->metas;
		$parsedHtml=$this->_parseHtml();
		
		// http://www.sagerock.com/blog/title-tag-meta-description-length/
		
		$metaTitle=$parsedHtml->find('head title');
		$this->check($metaTitle,'Meta title tags')->size(1);
		$metaTitle=$metaTitle[0]; $metaTitleText=hdecode($metaTitle->innertext);
		$c=$this->check($metaTitleText,'Meta title')->doubleSpace()->minLength(20);
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

/**
 * Test class
 * 
 * <code>
 * class UArrayTest extends STest{
	function firstValue(){
		$this->equals(UArray::firstValue(array(1)), 1);
		$this->equals(UArray::firstValue(array('2',3)), '2');
	}
 * }
 * </code>
 * 
 * @method STestCheck equals() (mixed $value,mixed $expected)
 * @method STestCheck isArray() (array $value)
 * @method STestCheck size() (array $value, int $size)
 * @method STestCheck contains() (array|string $value, int $string) the $value must contains $string
 * @method STestCheck isString() (string $value)
 * @method STestCheck maxLength() (string $value, int $maxLength)
 * @method STestCheck minLength() (string $value, int $minLength)
 * @method STestCheck doubleSpace() (string $value) the string should not have two consecutive space
 * 
 */
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
			try{
				$result = $this->$testMethod();
				if(empty($result)) $result='pass';
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
	
	public function currentNavigator(){
		return $this->lastNavigator;
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
	
	/**
	 * Check a value
	 * 
	 * @param mixed
	 * @return STestCheck
	 */
	public function check($value,$varInfo=null){
		return new STestCheck($this,$value,$varInfo);
	}
	
	public function __call($method,$args){
		return call_user_func_array(array(new STestCheck($this,array_shift($args)),$method),$args);
	}
	
	public function ex($message,$details){
		throw new SDetailedException($message.(empty($this->lastNavigator) || !$this->lastNavigator->getCurrentUrl()?'':"\n".'Last URL='.$this->lastNavigator->getCurrentUrl()),0,null,$details);
	}
}

