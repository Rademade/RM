<?php
class RM_View_Message
    implements
        Countable {
    
    private static $_self;
    
    private $_messages = array();
    
    private function __construct() {}
    
    /**
     * @static
     * @return static
     */
    public static function getInstance() {
        if (!(self::$_self instanceof self)) {
            self::$_self = new static();
        }
        return self::$_self;
    }
    
    public function add($message) {
        $this->_formatMessage( $message );
    }
    
    private function _formatMessage( $message ) {
        if ( is_array($message) ) {
            $this->_formatArray( $message );
        } else {
            if (is_string( $message )) {
                $this->_formatString( $message );
            } else {
                if ($message instanceof RM_Exception) {
                    $this->_formatArray($message->getMessages());
                } else {
                    $this->_formatString( $message->getMessage() );
                }
            }
        }
    }
    
    private function _formatString($message) {
        foreach ($this->getMessages() as $mes) {
            if ($message === $mes) {
                return;
            }
        }
        array_push($this->_messages, $message);
    }
    
    private function _formatArray($messages) {
        foreach ($messages as $message) :
            $this->_formatString( $message );
        endforeach;
    }
    
    public function getMessages() {
        return $this->_messages;
    }
    
    public function count() {
        return sizeof($this->_messages);
    }

}