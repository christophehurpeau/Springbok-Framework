<?php
if(!class_exists('Preprocessor',false))
	include CORE.'enhancers/files/Preprocessor.php';
class PreprocessorTest extends STest{
	
	function ifDev(){
		$preprocessor=new Preprocessor('js');
		$result=$preprocessor->process(array('DEV'=>true),"/*#if DEV*/alert('test');\n/*#endif*/");
		$this->equals($result,"alert('test');\n");
		$result=$preprocessor->process(array('PROD'=>false),"/*#if PROD*/\nalert('test');\n/*#endif*/");
		$this->equals($result,"");
	}
	
	function app(){
		$preprocessor=new Preprocessor('php');
		$result=$preprocessor->process(array('DEV'=>true,'PROD'=>false),"/*#if DEV */ini_set('display_errors',1);/*#/if*/
/*#if PROD*/ini_set('display_errors',0);/*#/if*/
error_reporting(E_ALL/* | E_STRICT*/);

include CORE.'springbok.php';");
		$this->equals($result,"ini_set('display_errors',1);

error_reporting(E_ALL/* | E_STRICT*/);

include CORE.'springbok.php';");
	}
	
	function ifNot(){
		$preprocessor=new Preprocessor('js');
		$result=$preprocessor->process(array('DEV'=>false),"/*#if !DEV*/alert('test');\n/*#endif*/");
		$this->equals($result,"alert('test');\n");
		$result=$preprocessor->process(array('DEV'=>true),"/*#if ! DEV*/\nalert('test');\n/*#endif*/");
		$this->equals($result,"");
	}
	
	function withBackslash(){
		$preprocessor=new Preprocessor('php');
		$result=$preprocessor->process(array('DEV'=>true),"/* /*#if DEV*/@Test('test')/*#endif*/ */");
		$this->equals($result,"/* @Test('test') */");
	}
	
	function ifThen(){
		$preprocessor=new Preprocessor('php');
		$str='public function /*#if DEV then _*/doSelectRowsCallback($query,$callback){}';
		$result=$preprocessor->process(array('DEV'=>true),$str);
		$this->equals($result,'public function _doSelectRowsCallback($query,$callback){}');
		$result=$preprocessor->process(array('DEV'=>false),$str);
		$this->equals($result,'public function doSelectRowsCallback($query,$callback){}');
	}

	function value(){
		$preprocessor=new Preprocessor('php');
		$result=$preprocessor->process(array('size'=>1),'$test->limit(/*#val size */);');
		$this->equals($result,'$test->limit(1);');
		$result=$preprocessor->process(array('size'=>1),'$test->limit(/*#val size */0);');
		$this->equals($result,'$test->limit(1);');
		$result=$preprocessor->process(array('size'=>1),'$test->limit(/*#val size */0,123);');
		$this->equals($result,'$test->limit(1,123);');
		$result=$preprocessor->process(array('arg1'=>'"ahah"'),'$test->limit(/*#val arg1 */\'\',123);');
		$this->equals($result,'$test->limit("ahah",123);');
		$result=$preprocessor->process(array('arg1'=>false),'$test->limit(/*#val arg1 */true,123);');
		$this->equals($result,'$test->limit(false,123);');
		$result=$preprocessor->process(array('arg1'=>true),'$test->limit(/*#val arg1 */false,123);');
		$this->equals($result,'$test->limit(true,123);');
		$result=$preprocessor->process(array('blog.VPostsLatest.fullUrls'=>true),'$post->excerpt=UHtml::transformInternalLinks($post->excerpt,Config::$internalLinks,\'index\',/*#val blog.VPostsLatest.fullUrls */false);');
		$this->equals($result,'$post->excerpt=UHtml::transformInternalLinks($post->excerpt,Config::$internalLinks,\'index\',true);');
	}
	
	function ifelse(){
		$preprocessor=new Preprocessor('js');
		$result=$preprocessor->process(array('DEV'=>true),'/*#ifelse DEV*/(module.exports||S.behaviours.Slug)/*#/if*/');
		$this->equals($result,'module.exports');
		$result=$preprocessor->process(array('DEV'=>false),'/*#ifelse DEV*/module.exports||S.behaviours.Slug/*#/if*/');
		$this->equals($result,'S.behaviours.Slug');
	}
}
