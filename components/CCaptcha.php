<?php
include CLIBS.'phpcaptcha/php-captcha.inc.php';
class CCaptcha{
	public static function image(){
		 $imagesPath = CLIBS.'phpcaptcha/fonts/';
		 $aFonts = array(
		 	$imagesPath.'VeraBd.ttf',
		 	$imagesPath.'VeraIt.ttf',
		 	$imagesPath.'Vera.ttf'
		 );
		 $oVisualCaptcha = new PhpCaptcha($aFonts,200,60);
		 $oVisualCaptcha->UseColour(false);
		 //$oVisualCaptcha->SetOwnerText('Source: '.FULL_BASE_URL);
		 //$oVisualCaptcha->SetMinFontSize(10);
		 //$oVisualCaptcha->SetNumChars(3);
		 $oVisualCaptcha->Create();
	}
	
	public static function audio(){
		$oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', '/tmp/');
		$oAudioCaptcha->Create();
	}
	
	public static function check($caseInsensitive=false){
		$userCode=$_POST[$key='captcha'];
		
		if($caseInsensitive) $userCode=strtoupper($userCode);
		
		if(CSession::exists(CAPTCHA_SESSION_ID) && $userCode == CSession::get(CAPTCHA_SESSION_ID)){
			CSession::remove(CAPTCHA_SESSION_ID);
			return true;
		}
		CValidation::addError($key,_t('Bad captcha'));
		return false;
	}
}
