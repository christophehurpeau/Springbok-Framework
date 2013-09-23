<?php
include CLIBS.'phpcaptcha/php-captcha.inc.php';
/**
 * Captcha
 * 
 * You must download http://www.ejeliot.com/pages/2 into the core libs folder
 * 
 * controller :
 * <code>
 * class SiteController extends Controller{
 * 	/* @ImportAction('core','Site','captchaImage') *\/
 * }
 * </code>
 * 
 * view :
 * <code>
 * <p><img id="captchaImg" class="vaMiddle" src="<? HHtml::url('/site/captchaImage') ?>"/>
 * <a href="javascript:void(0);" onclick="javascript:document.images.captchaImg.src='<? HHtml::url('/site/captchaImage') ?>?' + Math.round(Math.random(0)*1000)+1">{t 'plugin.contactForm.ChangeCaptcha'}</a></p>
 * {=$form->input('captcha')->label(_t('plugin.contactForm.Captcha:'))->size(60)}
 * </code>
 * 
 */
class CCaptcha{
	
	/**
	 * Create and render an image
	 */
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
	
	/**
	 * Create and render an audio
	 */
	public static function audio(){
		$oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', '/tmp/');
		$oAudioCaptcha->Create();
	}
	
	/**
	 * Check if the captcha is correct
	 * 
	 * @param bool
	 * @return bool if the captcha is correct
	 */
	public static function check($caseInsensitive=false){
		if(!isset($_POST[$key='captcha'])) return false;
		$userCode=$_POST[$key];
		
		if($caseInsensitive) $userCode=strtoupper($userCode);
		
		if(CSession::exists(CAPTCHA_SESSION_ID) && $userCode == CSession::get(CAPTCHA_SESSION_ID)){
			CSession::remove(CAPTCHA_SESSION_ID);
			return true;
		}
		CValidation::addError($key,_tC('Incorrect captcha'));
		return false;
	}
}
