<?php
abstract class RM_Mail {
	
	protected $_cnf;
    protected $_viewData = array();

    private $transport;

    public function __construct() {
		$this->_cnf = Zend_Registry::get("cfg");
        $transport = 'Zend_Mail_Transport_Smtp';
        if ( isset($this->_cnf['mail']['transport']['type']) ) {
            $transport = $this->_cnf['mail']['transport']['type'];
        }
		$this->transport = new $transport(
			$this->_cnf['mail']['transport']['host'],
			$this->_cnf['mail']['transport']['cfg']
		);
	}

    /**
     * @return RM_Mail_Message
     */
    protected function getMail() {
		$mail = new RM_Mail_Message('utf-8');
		$mail->setFrom($this->_cnf['mail']['from'], $this->_cnf['mail']['fromName']);
		return $mail;
	}
	
	protected function sendMail(RM_Mail_Message $mail) {
        if ( isset($this->_cnf['mail']['remote-transport']) ) {
            $mail->setRemoteTransportConfig( RM_Mail_Message_RemoteTransport::init( $this->_cnf['mail']['remote-transport'] ) );
        }
        $mail->send($this->transport);
	}
	
	protected function getView() {
		$view = new Zend_View();
		$view->setScriptPath($this->_cnf['mail']['viewPath']);
        if (isset($this->_cnf['mail']['view']['helperPath'])) {
			$view->setHelperPath( $this->_cnf['mail']['view']['helperPath'] );
		} else {
            $view->setHelperPath( APPLICATION_PATH . "/modules/admin/views/helpers" );
        }
        if (!empty($this->_viewData)) {
            $view->assign( $this->_viewData );
        }
		return $view;
	}
	
	abstract public function getText();
	abstract public function send($email);
		
}