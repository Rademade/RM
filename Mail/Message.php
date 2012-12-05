<?php
class RM_Mail_Message
    extends
        Zend_Mail {

    /**
     * @var RM_Mail_Message_RemoteTransport
     */
    protected $_remoteTransportConfig;

    /**
     * @var string
     */
    protected $_decodedSubject;

    /**
     * @param string $subject
     * @return RM_Mail_Message
     */
    public function setSubject($subject) {
        $this->_decodedSubject = $subject;
        parent::setSubject( $subject );
        return $this;
    }

    /**
     * @return string
     */
    public function getDecodedSubject() {
        return $this->_decodedSubject;
    }

    /**
     * @param RM_Mail_Message_RemoteTransport $sendConfig
     */
    public function setRemoteTransportConfig(RM_Mail_Message_RemoteTransport $sendConfig) {
        $this->_remoteTransportConfig = $sendConfig;
    }

    /**
     * @return RM_Mail_Message_RemoteTransport
     */
    public function getRemoteTransportConfig() {
        return $this->_remoteTransportConfig;
    }

}