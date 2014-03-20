<?php
class RM_Mail_Message_Background {

    private $_mail;
    private $_transport;

    public static function sendPool() {
        if (!is_null($file_name = self::__getFirstFile())) {
            $file_path = self::__getSavePath() . $file_name;
            /* @var RM_Mail_Message_Background $message */
            $message = unserialize( file_get_contents( $file_path ) );
            unlink( $file_path );
            $message->send();
            self::sendPool();
        }
    }
    
    public static function add(
        RM_Mail_Message $mail,
        Zend_Mail_Transport_Abstract $transport
    ) {
        $message = new self($mail, $transport);
        $message->save();
    }

    public function __construct(RM_Mail_Message $mail, Zend_Mail_Transport_Abstract $transport) {
        $this->_mail = $mail;
        $this->_transport = $transport;
    }

    public function save() {
        $save_path = self::__getSavePath();
        $file_name = time() . '-' . uniqid();
        file_put_contents($save_path . $file_name, serialize($this));
    }

    public function send() {
        $this->_mail->send( $this->_transport );
    }

    protected static function __getSavePath() {
        return Zend_Registry::get('cfg')['mail']['background']['path'] . '/';
    }

    protected static function __getFirstFile() {
        $dir_path = self::__getSavePath();
        $h = opendir( $dir_path );
        while (false !== ($entry = readdir($h))) {
            if( substr($entry, 0, 1) != '.' ) {
                return $entry;
            }
        }
        return null;
    }

}