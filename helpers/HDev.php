<?php
class HDev{
	public static function springbokBar(){
		if(CHttpRequest::isMobile()) return;
		echo HHtml::cssInline(file_get_contents(CORE.'includes/springbokBar.css'));
		echo HHtml::jsInline(file_get_contents(CORE.'includes/springbokBar.js'));
		echo '<div id="springbok-bar"><b onclick="$(\'#springbok-bar\').fadeOut()">Springbok</b>'
			.' | <a href="javascript:;" rel="changes">Changes ('.(empty(App::$changes) || empty(App::$changes[0][1]['all']) ?'0':count(App::$changes[0][1]['all'])).')</a>'
			.' | <a href="javascript:;" rel="queries">Queries ('.(!class_exists('DB',false)?'0':(array_sum(array_map(function(&$db){return count($db->getQueries());},DB::getAll())))).')</a>'
			.' | <a href="javascript:;" rel="route">Route</a>'
			.' | <a href="javascript:;" rel="ajax">Ajax (<span>0</span>)</a>'
			.'</div>';
		
		echo '<div id="springbok-bar-changes" class="springbok-bar-content"><div>';
		self::springbokBarChanges();
		echo '</div></div><div id="springbok-bar-queries" class="springbok-bar-content"><div>';
		self::springbokBarQueries();
		echo '</div></div><div id="springbok-bar-route" class="springbok-bar-content"><div>';
		self::springbokBarRoute();
		echo '</div></div><div id="springbok-bar-ajax" class="springbok-bar-content"><div>';
			echo '<div style="float:left;width:300px"><ul class="clickable nobullets spaced"></ul></div>'
					.'<div id="SpringbokBarAjaxContent" style="margin-left:310px">';
		echo '</div></div>';
		
		echo '</div></div><div id="springbok-bar-console" class="springbok-bar-content"><div>';
		echo '</div></div>';
		
		/*echo '<div id="springbok-bar-popup"><a href="javascript:;" onclick="$(\'#springbok-bar-popup\').fadeOut()">Close</a><pre></pre></div>';*/
	}
	
	private static function springbokBarChanges(){
		$changes=&App::$changes[0];
		echo '<h2'.($changes?($changes[2]?' style="color:red"':($changes[3]?' style="color:orange"':'')):'').'>Changes</h2>';
		if(!empty($changes)){
			echo '<div class="italic">Enhancing took : <b>'.App::$changes[0][0].'</b> s</div>';
			foreach(App::$changes[0][1] as $type=>$files){
				echo '<h5 class="sepTop">'.$type.'</h5><ul class="compact">';
				foreach($files as $file) echo '<li>'.(is_array($file)?$file['path']:$file).'</li>';
				echo '</ul>';
			}
		}
	}
	
	private static function springbokBarQueries(){
		if(!class_exists('DB',false)) return;
		
		echo HHtml::cssInline(file_get_contents(CORE.'includes/debug.css'));
		foreach(DB::getAll() as $dbname=>$db){
			$queries=$db->getQueries();
			echo '<table class="debug">';
			echo '<tr class="info"><td colspan="3"><b>'.$dbname.'</b> - '.($totalQuery=count($queries)).' queries</td></tr>';
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
					echo '<pre>';var_dump($query['result']);echo '</pre>';
				echo '</div></td><td class="time">'.number_format($query['time']*1000,0,'',' ').' ms</td></tr>';
			}
			echo '</table>';
		}
	}

	private static function springbokBarRoute(){
		echo '<h2>Route</h2>';
		echo '<div>Controller = "'.CRoute::getController().'"</div>';
		echo '<div>Action = "'.CRoute::getAction().'"</div>';
		echo '<div>Ext = '.short_debug_var(CRoute::getExt()).'</div>';
		echo '<div>Params = '.print_r(CRoute::getParams(),true).'</div>';
		echo '<div>Tested routes = <pre>'."\n\t"
			.implode("\n\t",CRoute::$TESTED_ROUTES)
			.'</pre></div>';
	}

	public static function error(&$e_message,&$e_file,&$e_line,&$e_context){
		echo '<pre style="white-space:pre-wrap; word-wrap:break-word">'.h($e_message).' ('.geditURL($e_file,$e_line).replaceAppAndCoreInFile($e_file).':'.$e_line.'</a>)'.'</pre>';
		if($e_file && $e_file !== 'Unknown'){
			echo '<br/><h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">File content:</h5>';
			echo HText::highlightLine(file_get_contents($e_file),'php',$e_line,false,'background:#EBB',true,14,array('style'=>'font-family:\'Ubuntu Mono\',\'UbuntuBeta Mono\',Monaco,Menlo,"Courier New",monospace;font-size:9pt;'));
		}
		echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Call Stack:</h5><pre>'.prettyHtmlBackTrace(3).'</pre>';
		
		if(!empty($e_context)) echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Context:</h5><pre>'.h2(short_debug_var($e_context)).'</pre>';
	}
	
	public static function exception(&$e_message,&$e_file,&$e_line,&$e_trace){
		echo '<pre style="white-space:pre-wrap; word-wrap:break-word">'.h($e_message).' ('.geditURL($e_file,$e_line).replaceAppAndCoreInFile($e_file).':'.$e_line.'</a>)'.'</pre>';
		if($e_file && $e_file !== 'Unknown'){
			echo '<br/><h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">File content:</h5>';
			echo HText::highlightLine(file_get_contents($e_file),'php',$e_line,false,'background:#EBB',true,10);
		}
		echo '<h5 style="background:#FFDDAA;color:#333;border:1px solid #E07308;padding:1px 2px;">Call Stack:</h5><pre>'.prettyHtmlBackTrace(0,$e_trace).'</pre>';
	}
}
