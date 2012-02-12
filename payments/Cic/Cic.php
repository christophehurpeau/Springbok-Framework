<?php
class Cic extends Payment{
	public $bankServer='https://ssl.paiement.cic-banques.fr/paiement.cgi',
		$ctlhmac,
		$passphrase,
		$sitecode,
		$tpenum,
		$urlback,
		$urlback_failed,
		$urlback_success;
	
	public function description(){
		$this->render('description');
	}
	
	
}
