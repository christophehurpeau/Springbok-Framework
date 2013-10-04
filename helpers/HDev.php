<?php
/**
 * Dev Helper
 */
class HDev{
	/**
	 * Display the Springbok Bar.
	 * 
	 * This function is always called before the html end tag (</html>)
	 * But you can use it if you want to debug things :
	 * 
	 * <code>
	 * static function test(){
	 * 	$posts = Post::QAll()->limit(4);
	 * 	HDev::springbokBar(true);
	 * 	exit;
	 * 	render();
	 * }
	 * </code>
	 * 
	 * @param bool
	 * @return void
	 */
	public static function springbokBar($includeJquery=false){
		/*#if PROD*/ return; /*#/if*/
		if(CHttpUserAgent::isMobileAndNotTablet() || isset($_GET['springbokNoDevBar'])) return;
		if(defined('CORE_SRC')){
			if($includeJquery){
				echo "<script type=\"text/javascript\">\n//<![CDATA[\n";
				readfile(CORE_SRC.'includes/js/libs/jquery-1.8.3.js');
				echo "//]]>\n</script>";
			}
			echo HHtml::cssInline(file_get_contents(CORE_SRC.'includes/springbokBar.css'));
			echo HHtml::jsInline('$(document).ready(function(){'.file_get_contents(CORE_SRC.'includes/js/jquery/json.js').file_get_contents(CORE_SRC.'includes/springbokBar.js').'});');
		}
		$changes=&App::$changes[0];
		echo '<div id="springbok-bar"><a href="#" class="springbokTitle" onclick="if(confirm(\'Voulez-vous cacher SpringbokBar ?\')) $(\'#springbok-bar\').fadeOut()"><b>Springbok</b></a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="changes">Changes ('.(file_exists(dirname(APP).'/block_deploy')?'<span style="color:red;font-weight:bold">A deployment is in progress':
									('<span'.($changes?($changes[2]?' style="color:red"':($changes[3]?' style="color:orange"':'')):'').'>'
										.empty($changes) || empty($changes[0][1]['all']) ?'0':count($changes[0][1]['all']))).'</span>)</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="queries">Queries ('.(!class_exists('DB',false)?'0':(array_sum(array_map(function(&$db){return $db->getNbQueries();},DB::getAll())))).')</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="route">Route</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="sessiotn">Session</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="js-console">Js Console (<span>0</span>)</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="ajax">Ajax (<span>0</span>)</a>'
			.'<span class="springokBarSep"> | </span><a href="javascript:;" rel="metas">Métas</a>'
			.'</div>';
		
		echo '<div id="springbok-bar-changes" class="springbok-bar-content"><div>'; self::springbokBarChanges(); echo '</div></div>';
		echo '<div id="springbok-bar-queries" class="springbok-bar-content"><div>'; self::springbokBarQueries(); echo '</div></div>';
		echo '<div id="springbok-bar-route" class="springbok-bar-content"><div>'; self::springbokBarRoute(); echo '</div></div>';
		echo '<div id="springbok-bar-session" class="springbok-bar-content"><div>'; self::springbokBarSession(); echo '</div></div>';
		
		echo '<div id="springbok-bar-ajax" class="springbok-bar-content"><div>';
			echo '<div style="float:left;width:300px"><ul class="clickable spaced"></ul></div>'
					.'<div id="SpringbokBarAjaxContent" style="margin-left:310px"></div>';
		echo '</div></div>';
		
		echo '<div id="springbok-bar-js-console" class="springbok-bar-content"><ul class="nobullets spaced"></ul></div>';
		echo '<div id="springbok-bar-metas" class="springbok-bar-content"><div>'; self::springbokBarMetas(); echo '</div></div>';
		
		/*echo '<div id="springbok-bar-popup"><a href="javascript:;" onclick="$(\'#springbok-bar-popup\').fadeOut()">Close</a><pre></pre></div>';*/
	}
	
	private static function springbokBarChanges(){
		$changes=&App::$changes[0];
		echo '<h2'.($changes?($changes[2]?' style="color:red"':($changes[3]?' style="color:orange"':'')):'').'>Changes</h2>';
		if(!empty($changes)){
			echo '<div class="italic">Enhancing took : <b>'.App::$changes[0][0].'</b> s</div>';
			foreach(App::$changes[0][1] as $type=>$files){
				echo '<h5 class="sepTop">'.$type.'</h5><ul class="compact">';
				foreach($files as $file) echo '<li>'.(is_array($file)?$file['path'].(!isset($file['time'])?'':' ('.$file['time'].')'):$file).'</li>';
				echo '</ul>';
			}
		}
	}
	
	/**
	 * Return all the queries executed
	 * Usefull for debugging with debug or print_r
	 * 
	 * @return array
	 * 
	 * @see debug
	 */
	public static function queries(){
		$queries=array();
		foreach(DB::getAll() as $dbname=>$db){
			$dbqueries=$db->getQueries();
			foreach($dbqueries as $query) $queries[$dbname][]=$query['query'];
		}
		return $queries;
	}
	
	private static function springbokBarQueries(){
		if(!class_exists('DB',false)) return;
		
		echo HHtml::cssInline(file_get_contents(CORE_SRC.'includes/debug.css'));
		foreach(DB::getAll() as $dbname=>$db){
			$queries=$db->getQueries();
			echo '<table class="debug">';
			echo '<tr class="info"><td colspan="3"><b>'.$dbname.'</b> - '.($totalQuery=$db->getNbQueries()).' queries</td></tr>';
			$irow=0;
			foreach($queries as $query){
				echo '<tr class="';
				if(++$irow==0) echo 'first_item';
				//elseif($irow==$totalQuery-1) echo 'last_item';
				elseif($irow%2) echo 'alternate_item';
				else echo 'item';
				echo '"><td class="iteration">'.$irow.'</td>'
					.'<td><div class="query"><a href="javascript:;" onclick="$(this).parent().parent().find(\'.result\').slideToggle()">'.h($query['query']).'</a></div>'
						.'<div class="result"><pre>'.prettyBackTrace(0,$query['backtrace']).'</pre>';
				if(!empty($query['result']))
					echo '<pre>'.UVarDump::dump($query['result']).'</pre>';
				echo '</div></td><td class="time">'.number_format($query['time']*1000,0,'',' ').' ms</td></tr>';
			}
			echo '</table>';
		}
	}

	private static function springbokBarRoute(){
		echo '<h2>Route</h2>';
		echo '<div>Controller = "'.CRoute::getController().'"</div>';
		echo '<div>Action = "'.CRoute::getAction().'"</div>';
		echo '<div>Ext = '.UVarDump::dump(CRoute::getExt()).'</div>';
		echo '<div>Params = '.print_r(CRoute::getParams(),true).'</div>';
		echo '<div>Tested routes = <pre>'."\n\t"
			.implode("\n\t",CRoute::$TESTED_ROUTES)
			.'</pre></div>';
	}
	
	private static function springbokBarSession(){
		echo '<h2>Session</h2>';
		if(!class_exists('CSession',false)) echo "not started";
		elseif(!isset($_SESSION)) echo "closed";
		else{
			echo UVarDump::dump($_SESSION);
		}
	}
	
	private static function springbokBarMetas(){
		echo '<h2>Métas</h2>';
		echo '<ul id="springbokbar-metas-content"></ul>';
		echo '<script>$(document).ready(function(){ var c=$("#springbokbar-metas-content");'
			.' $("meta").each(function(e){ var li=$("<li>"),e=$(e); li.text("name="+e.attr("name")+", property="+e.attr("property")+", content="+e.attr("content")); c.append(li); })'
			.' })</script>';
	}
	
	
	/**
	 * Display warnings and other stuffs just after the body.
	 * 
	 * This function is always called after the bdoy start tag (<body>)
	 * 
	 * @return void
	 */
	public static function body(){
		if(!empty(App::$enhanceApp) && App::$enhanceApp->hasWarnings()){
			debugNoFlush(App::$enhanceApp->getWarnings());
		}
	}
	
	
	
	/**
	 * Display an error
	 * 
	 * @param string
	 * @param string
	 * @param int
	 * @param mixed
	 * @return void
	 */
	public static function error(&$e_message,&$e_file,&$e_line,&$e_context){
		echo '<pre style="background:#FFF;color:#222;border:0;font-size:1em;white-space:pre-wrap;word-wrap:break-word">'.h($e_message).' ('.openLocalFile($e_file,$e_line).replaceAppAndCoreInFile($e_file).':'.$e_line.'</a>)'.'</pre>';
		if($e_file && $e_file !== 'Unknown' && file_exists($e_file)){
			echo '<br/><h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">File content:</h5>';
			echo HText::highlightLine(file_get_contents($e_file),'php',$e_line,false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:1em;'));
		}
		echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Call Stack:</h5><pre style="background:#FFF;color:#222;border:0">'.prettyHtmlBackTrace(3).'</pre>';
		
		if(!empty($e_context)) echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Context:</h5><pre style="background:#FFF;color:#222;border:0">'.UVarDump::dump($e_context).'</pre>';
	}
	
	/**
	 * Display an exception
	 * 
	 * @param Exception
	 * @return void
	 */
	public static function exception(Exception $e){
		echo '<pre style="background:#FFF;color:#222;border:0;font-size:1em;white-space:pre-wrap;word-wrap:break-word">'.h($e instanceof SDetailedException ? $e->getTitle() : $e->getMessage())
					.' ('.openLocalFile($e->getFile(),$e->getLine()).replaceAppAndCoreInFile($e->getFile()).':'.$e->getLine().'</a>)'.'</pre>';
		if($e instanceof SDetailedException)
			echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Details:</h5>'.$e->detailsHtml();
		if($e->getFile() && $e->getFile() !== 'Unknown'){
			echo '<br/><h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">File content:</h5>';
			echo HText::highlightLine(file_get_contents($e->getFile()),'php',$e->getLine(),false,'background:#EBB',true,10);
		}
		echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Call Stack:</h5><pre style="background:#FFF;color:#222;border:0">'.prettyHtmlBackTrace(0,$e->getTrace()).'</pre>';
		
		$previous=$e->getPrevious();
		if(!empty($previous)){
			$e=$previous;
			
			echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Previous exception</h5>'
					.'<pre style="background:#FFF;color:#222;border:0;font-size:1em;white-space:pre-wrap;word-wrap:break-word">'.h($e instanceof SDetailedException ? $e->getTitle() : $e->getMessage())
						.' ('.openLocalFile($e->getFile(),$e->getLine()).replaceAppAndCoreInFile($e->getFile()).':'.$e->getLine().'</a>)'.'</pre>'
					.'<pre style="background:#FFF;color:#222;border:0">'.prettyHtmlBackTrace(0,$e->getTrace()).'</pre>';
		}
	}
}
