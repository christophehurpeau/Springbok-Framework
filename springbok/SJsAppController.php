<?php
class SJsAppController extends Controller{
	public static function beforeDispatch(){
		if(!CHttpRequest::isAjax()) self::renderStartPage();
	}
	
	protected static function renderStartPage(){
		echo '<!DOCTYPE html><html><head>'
			.'<meta charset="UTF-8">'
			.'<title>'.Config::$projectName.' - '.($loading=_tC('Loading...')).'</title>';
		HHtml::cssLink();
		echo HHtml::jsInline(
			'var i18n_lang="'.CLang::get().'";'
			.'window.onload=function(){'
				
				.'var script=document.createElement("script");'
				.'script.type="text/javascript";'
				.'script.src="'.HHtml::staticUrl('/jsapp'.'.js','js').'";'
				.'document.body.appendChild(script);'
			.'};'
		);
		echo '</head><body>'
			.'<div id="container"><div class="startloading"><b>'.Config::$projectName.'</b><br/>'.($loading).'</div></div>'
			.'</body></html>';
		exit;
	}
}
