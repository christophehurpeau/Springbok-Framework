<?php
class SJsAppController extends Controller{
	protected static function renderStartPage($jsappScript='/jsapp',$cssLink='/main'){
		echo '<!DOCTYPE html><html><head>'
			.HHtml::metaCharset().HHtml::metaLanguage()
			.'<title>'.Config::$projectName.' - '.($loading=_tC('Loading...')).'</title>'
			.HHtml::jsCompat();
		HHtml::cssLink($cssLink);
		echo HHtml::jsInline(
			'window.onload=function(){'
				.'var s=document.createElement("script");'
				.'s.type="text/javascript";'
				.'s.src="'.HHtml::staticUrl($jsappScript.'.js','js')/* DEV */.'?'.time()/* /DEV */.'";'
				.'document.body.appendChild(s);'
			.'};'
		);
		echo '</head><body>'
			.'<div id="container"><div class="startloading"><b>'.Config::$projectName.'</b><div id="jsAppLoadingMessage">'.($loading).'</div></div></div>'
			.'</body>';
		//HDev::springbokBar();
		echo '</html>';
		exit;
	}
}
