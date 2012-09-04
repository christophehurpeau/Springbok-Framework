<?php
include CLIBS.'PHPMailer/class.phpmailer.php';
class CMail{
	private static $_instance;
	
	public static function init($suffix){
		self::$_instance=new PHPMailer();
		$_config=App::configArray('mail'.$suffix);
		foreach($_config as $key=>&$val) self::$_instance->$key=$val;
	}
	
	/**
	 * @return PHPMailer
	 */
	public static function get(){
		if(self::$_instance===null) self::init(Springbok::$suffix);
		return self::$_instance;
	}
	
	public static function send($template,$vars,$subject,$to){
		return self::create($template,$vars,$subject,$to)->Send();
	}
	
	public static function create($template,$vars,$subject,$to){
		if(!isset($vars['subject'])) $vars['subject']=$subject;
		include_once CORE.'mvc/views/View.php';
		$mailer=self::get();
		$mailer->ClearAllRecipients();
		$mailer->ClearAttachments();
		$mailer->AddAddress($to);
		$mailer->Subject=$subject;
		if(!empty($vars['email'])) $mailer->AddReplyTo($vars['email']);
		$html=render(APP.'viewsMails/'.$template.'.php',$vars,true);
		$html=preg_replace('#\s*<(/?(?:li|ul|ol|div|p|table|tr)|td|body|html|head)(\s+|>)#iu',"\n<$1$2",$html);
		$html=preg_replace('#\n(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?#iu',"\n$1$2$3$4$5\n",$html);
		$mailer->MsgHTML($html,APP);
		return $mailer;
	}
	
	public static function sendAdmin($template,$vars,$subject){
		self::send('admin/'.$template, $vars, $subject,Config::$admin_email);
	}
}