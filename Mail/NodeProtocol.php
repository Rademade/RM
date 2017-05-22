<?php
class RM_Mail_NodeProtocol extends Zend_Mail_Protocol_Abstract {

    protected $_transport = 'tcp';

    /**
     * @var RM_Mail_Message_RemoteTransport
     */
    protected $_remoteTransportConfig;

    protected $_sess = false;

    //mail data
    protected $_sendFrom;
    protected $_sendTo = array();
    protected $_mailSubject;
    protected $_mailBody;

    public function __construct($host = '127.0.0.1', $port = 1337) {
        parent::__construct($host, $port);
    }

    public function connect() {
        return $this->_connect($this->_transport . '://' . $this->_host . ':'. $this->_port);
    }

    public function helo($host = '127.0.0.1')  {
        if (!$this->_validHost->isValid($host)) {
            throw new Zend_Mail_Protocol_Exception(join(', ', $this->_validHost->getMessages()));
        }
        $this->_expect(200, 10);
        $this->_startSession();
    }

    public function sendFrom($from) {
        //TODO validate
        $this->_sendFrom = $from;
    }

    /**
     * @param $to
     */
    public function setSendTo($to) {
        if (!is_array($to)) {
            $to = array( $to );
        }
        $this->_sendTo = $to;
    }

    public function setSubject($subject) {
        //TODO validate
        $this->_mailSubject = $subject;
    }

    public function setMailBody($mailBody) {
        //TODO validate
        $this->_mailBody = $mailBody;
    }

    public function setRemoteTransportConfig(RM_Mail_Message_RemoteTransport $remoteTransportConfig) {
        $this->_remoteTransportConfig = $remoteTransportConfig;
    }

    /**
     * @return RM_Mail_Message_RemoteTransport
     */
    public function getRemoteTransportConfig() {
        return $this->_remoteTransportConfig;
    }

    public function send() {
        $this->_send( array(
            'command'   => 'send',
            'message'	=> array(
                'from'      => $this->_sendFrom,
                'to'        => $this->_sendTo,
                'subject'   => $this->_mailSubject,
                'body'      => $this->_mailBody
            ),
            'config'    => $this->getRemoteTransportConfig()->__toJSON()
        ) );
    }

    public function reset() {
        $this->_send( array(
            'command' => 'reset'
        ) );
        $this->_expect( 150 );
        //TODO clear params
    }

    public function verify($user) {
        //TODO
    }


    /**
     * Issues the QUIT command and clears the current session
     *
     * @return void
     */
    public function quit() {
        if ($this->_sess) {
            $this->_send( array(
                'command' => 'quit'
            ) );
            $this->_expect(300, 10); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
            $this->_stopSession();
        }
    }

    public function disconnect() {
        $this->_disconnect();
    }

    protected function _startSession() {
        $this->_sess = true;
    }

    protected function _stopSession() {
        $this->_sess = false;
    }

    protected function _send($data) {
        if (!is_array( $data ))
            throw new Exception('Data must be instance of array');
        if ( !isset( $data['command'] ) )
            throw new Exception('Data array must have command key');
        $jsonData = json_encode( $data );
        parent::_send( $jsonData );
    }

}
