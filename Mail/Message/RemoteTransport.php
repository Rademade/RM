<?php
class RM_Mail_Message_RemoteTransport {

    private $_host;

    private $_cfg;

    public function __construct($host, array $cfg = array()) {
        $this->_host = $this->_validateHost( $host );
        $this->_cfg = $this->_validateConfig( $cfg );
    }

    public static function init(array $cfg) {
        if ( !isset($cfg['host']) ) {
            throw new Exception('Parameter host not setted');
        }
        return new self(
            $cfg['host'],
            isset($cfg['cfg']) ? $cfg['cfg'] : array()
        );
    }

    protected function _validateHost($host) {
        //TODO validate host
        return $host;
    }

    protected function _validateConfig(array $config) {
        //TODO vaidate config
        return $config;
    }

    public function __toJSON() {
        return array_merge( array(
            'host'  => $this->_host,
        ), $this->_cfg );
    }

}
