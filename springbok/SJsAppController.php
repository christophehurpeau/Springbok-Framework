<?php
/** @deprecated */
class SJsAppController extends Controller{
	protected static function renderStartPage($jsappScript='/jsapp',$cssLink='/main'){
		echo '<!DOCTYPE html><html><head>'
			.HHtml::metaCharset().HHtml::metaLanguage()
			.'<title>'.Config::$projectName.' - '.($loading=_tC('Loading...')).'</title>';
		HHead::linkCss($cssLink);
		echo HHtml::jsInline(
			'window.onload=function(){'
				.'var s=document.createElement("script");'
				.'s.type="text/javascript";'
				.'s.src="'.HHtml::staticUrl($jsappScript.(CHttpUserAgent::isIElt9()?'.oldIe':'').'.js','js')/*#if DEV */.'?'.time()/*#/if*/.'";'
				.'document.body.appendChild(s);'
			.'};'
		);
		HHead::display();
		echo '</head><body>'
			.'<div id="container"><div class="startloading"><b>'.Config::$projectName.'</b><div id="jsAppLoadingMessage">'.($loading).'</div></div></div>'
			.'</body>';
		//HDev::springbokBar();
		echo '</html>';
		exit;
	}
}
