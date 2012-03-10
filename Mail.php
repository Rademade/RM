<?php
abstract class RM_Mail {
	
	protected $cnf;
	private $transport;
	
	public function __construct() {
		$this->cnf = Zend_Registry::get("cfg");
		$this->transport = new Zend_Mail_Transport_Smtp(
			$this->cnf['mail']['transport']['host'],
			$this->cnf['mail']['transport']['cfg']
		);
	}
	
	protected function getMail() {
		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($this->cnf['mail']['from'], $this->cnf['mail']['fromName']);
		return $mail;
	}
	
	protected function sendMail(Zend_Mail $mail) {
		$mail->send($this->transport);
	}
	
	protected function getView() {
		$view = new Zend_View();
		$view->setScriptPath($this->cnf['mail']['viewPath']);
		return $view;
	}
	
	abstract public function getText();
	abstract public function send($toEmail);
		
}