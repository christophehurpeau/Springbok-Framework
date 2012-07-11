<?php
include CLIBS.'PHPMailer/class.phpmailer.php';
class CMail{
	private static $_instance;
	
	public static function init(){
		self::$_instance=new PHPMailer();
		$_config=App::configArray('mail'.Springbok::$suffix);
		foreach($_config as $key=>&$val) self::$_instance->$key=$val;
	}
	
	/**
	 * @return PHPMailer
	 */
	public static function get(){
		return self::$_instance;
	}
	
	public static function send($template,$vars,$subject,$to){
		$vars['subject']=$subject;
		include_once CORE.'mvc/views/View.php';
		$mailer=self::get();
		$mailer->ClearAllRecipients();
		$mailer->AddAddress($to);
		$mailer->Subject=$subject;
		if(!empty($vars['email'])) $mailer->AddReplyTo($vars['email']);
		$mailer->MsgHTML(render(APP.'viewsMails/'.$template.'.php',$vars,true),APP);
		return $mailer->Send();
	}
	
	public static function sendAdmin($template,$vars,$subject){
		self::send('admin/'.$template, $vars, $subject,Config::$admin_email);
	}
}
CMail::init();