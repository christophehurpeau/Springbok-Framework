<?php
class SJsAppController extends Controller{
	protected static function renderStartPage(){
		echo '<!DOCTYPE html><html><head>'
			.'<meta charset="UTF-8">'
			.'<title>'.Config::$projectName.' - '.($loading=_tC('Loading...')).'</title>';
		HHtml::cssLink();
		echo HHtml::jsInline(
			'S.lang="'.CLang::get().'";'
			.'window.onload=function(){'
				.'var s=document.createElement("script");'
				.'s.type="text/javascript";'
				.'s.src="'.HHtml::staticUrl('/jsapp'.'.js','js')/* DEV */.'?'.time()/* /DEV */.'";'
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
