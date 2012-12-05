<?php
class RM_Mail_NodeTransport extends Zend_Mail_Transport_Smtp {

    /**
     * @var RM_Mail_NodeProtocol
     */
    protected $_connection;

    /**
     * @var RM_Mail_Message
     * @access protected
     */
    protected $_mail = false;

    public function _sendMail() {
        if (!($this->_connection instanceof RM_Mail_NodeProtocol)) {
            $this->setConnection(new RM_Mail_NodeProtocol($this->_host, $this->_port));
            $this->_connection->connect();
            $this->_connection->helo($this->_name);
        } else {
            $this->_connection->reset();
        }

        $this->_connection->sendFrom( $this->_mail->getReturnPath() );
        $this->_connection->setSendTo( $this->_mail->getRecipients() );
        $this->_connection->setSubject( $this->_mail->getDecodedSubject(  ) );
        $this->_connection->setRemoteTransportConfig( $this->_mail->getRemoteTransportConfig() );
        $this->_connection->setMailBody( $this->_mail->getBodyHtml()->getContent() );
        $this->_connection->send();
    }

    public function send(RM_Mail_Message $mail) {
        $this->_mail = $mail;
        parent::send($mail);
    }

}