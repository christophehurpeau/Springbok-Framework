<?php
class SiteController extends Controller{
	/** */
	static function index(){
		render();
	}
	
	/** */
	static function favicon(){
		self::cacheFor('3 weeks');
		renderFile(APP.'web/img/favicon.ico');
	}

	/** */
	static function appleTouchIconPrecomposed(){
		self::cacheFor('3 weeks');
		renderFile(APP.'web/img/logo-57.png');
	}

	
	/** */
	static function login(User $user){
		if(empty($_POST)) ACSecure::connect();
		elseif($user!==null && ACSecure::authenticate($user,true)) exit;
		else CSession::setFlash('Impossible de vous connecter : identifiant ou mot de passe invalide...','user/login');
		self::render();
	}
	
	/** */
	static function logout(){
		ACSecure::logout();
		self::redirect('/');
	}
	
	/** */
	static function captchaImage(){
		CCaptcha::image();
	}
	
	/** @ValidParams @Required('jsurl') */
	static function jsError($href,$jsurl,$message,$line){
		if($jsurl!=='http://www.google-analytics.com/ga.js'
			&&$jsurl!=='http://connect.facebook.net/fr_FR/all.js#xfbml=1'&&$jsurl!=='http://platform.twitter.com/widgets.js'
			&&$jsurl!=='http://pagead2.googlesyndication.com/pagead/show_ads.js')
			JsLog::create($href,$jsurl,$message,$line);
	}
	
}