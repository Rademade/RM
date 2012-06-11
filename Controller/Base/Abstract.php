<?php
abstract class RM_Controller_Base_Abstract
    extends
        Zend_Controller_Action {

    /**
     * @var int
     */
    protected $_idUser;

    /**
     * @var RM_User_Session
     */
    protected $_session;

    /**
     * @var RM_User_Interface
     */
    protected $_user;

    /**
     * @var RM_User_Profile
     */
    protected $_profile;

    public function preDispatch() {
        $this->__initSession();
        $this->__initProfile();
    }

    protected function __initSession() {
        if (!$this->_session instanceof RM_User_Session) {
            $this->_session = RM_User_Session::getInstance();
            $this->_user = $this->_session->getUser();
            $this->_idUser = $this->_user instanceof RM_User_Interface ? $this->_user->getId() : 0;
        }
    }

    protected function __initProfile() {
        if ($this->_user instanceof RM_User_Interface) {
            /* @var RM_User_Profile_Interface $model */
            $model = RM_Dependencies::getInstance()->userProfile;
            $this->_profile = $model::getByUser( $this->_user );
        }
    }

    protected function __isAdmin() {
        $this->__initSession();
        $this->__initProfile();
        return $this->_user instanceof RM_User_Interface && $this->_user->getRole()->isAdmin();
    }

    protected function __disableView() {
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function postDispatch() {
        $this->view->assign(array(
            'user' => $this->_user,
            'idUser' => $this->_idUser,
            'profile' => $this->_profile
        ));
    }

}