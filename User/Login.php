<?php
class RM_User_Login {

    /**
     * @var RM_User_Profile_Interface
     */
    private $_user;
    /**
     * @var RM_User_Session
     */
    private $_session;

    public function __construct(RM_User_Profile_Interface $user) {
        $this->_user = $user;
        $this->_session = RM_User_Session::getInstance();
    }

    public function getUser() {
        return $this->_user;
    }

    public function getSession() {
        return $this->_session;
    }

    public function createSession() {
        $this->_session->create( $this->getUser()->getUser() );
    }

    public function remember() {
        $this->getSession()->remember();
    }

    public function login($password, $remember) {
        if ($this->getUser()->checkPassword($password) || $password === false) {
            $this->createSession();
            if ($remember) {
                $this->remember();
            }
            return true;
        } else {
            return false;
        }
    }

    public static function logout() {
        RM_User_Session::getInstance()->logout();
    }

}
