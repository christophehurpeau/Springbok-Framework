<?php
class SiteController extends Controller{
	/** */
	function index(){
		 render();
	}
	
	/** */
	function favicon(){
		 renderFile(APP.'web/img/favicon.ico');
	}

	
	/** */
	function login(User $user){
		if(empty($_POST)) CSecure::connect();
		elseif($user!==null && CSecure::authenticate($user,true)) exit;
		else CSession::setFlash('Impossible de vous connecter : identifiant ou mot de passe invalide...','user/login');
		self::render();
	}
	
	/** */
	function logout(){
		CSecure::logout();
		self::redirect('/');
	}
	
	/** */
	function captchaImage(){
		CCaptcha::image();
	}
	
	/** @ValidParams @Required('jsurl') */
	function jsError($href,$jsurl,$message,$line){
		if($jsurl!=='http://www.google-analytics.com/ga.js'
			&&$jsurl!=='http://connect.facebook.net/fr_FR/all.js#xfbml=1'&&$jsurl!=='http://platform.twitter.com/widgets.js')
			JsLog::create($href,$jsurl,$message,$line);
	}
	
}