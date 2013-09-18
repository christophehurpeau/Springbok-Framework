<?php
include CLIBS.'PHPMailer/class.phpmailer.php';
/**
 * Send and create mails
 * 
 * <b>Config</b>
 * 
 * <code>
 * return array(
 * 	'From'=>'test@example.com',
 * 	'FromName'=>'Site de Test',
 * 	'ContentType'=>'text/html',
 * 'CharSet'=>'utf-8'
 * );
 * </code>
 * 
 */
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
		$mailer=self::_create($subject,$to);
		if(!empty($vars['email'])) $mailer->AddReplyTo($vars['email']);
		$html=render(APP.'viewsMails/'.$template.'.php',$vars,true);
		$html=preg_replace('#\s*<(/?(?:li|ul|ol|div|p|table|tr)|td|body|html|head)(\s+|>)#iu',"\n<$1$2",$html);
		$html=preg_replace('#\n(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?(?:(</?(?:li|ul|ol|div|p|a|table|tr|body|html|head)>)\n)?#iu',"\n$1$2$3$4$5\n",$html);
		$mailer->MsgHTML($html,APP);
		return $mailer;
	}
	
	private static function _create($subject,$to){
		$mailer=self::get();
		$mailer->ClearAllRecipients();
		$mailer->ClearAttachments();
		$mailer->ClearReplyTos();
		$mailer->AltBody='';
		if(is_array($to))
			foreach($to as $address) $mailer->AddBCC($address);
		else
			$mailer->AddAddress($to);
		$mailer->Subject=$subject;
		return $mailer;
	}
	
	public static function createHtml($html,$subject,$to){
		$mailer=self::_create($subject,$to);
		$mailer->MsgHTML($html,APP);
		return $mailer;
	}
	
	
	public static function sendHtml($html,$subject,$to){
		return self::createHtml($html,$subject,$to)->Send();
	}
	
	
	public static function sendAdmin($template,$vars,$subject){
		self::send('admin/'.$template, $vars, $subject,Config::$admin_email);
	}
	
	
	public static function sendAdminHtml($html,$subject){
		self::sendHtml($html,$subject,Config::$admin_email);
	}
}