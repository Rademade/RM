<?php
abstract class RM_Controller_Admin
    extends
        RM_Controller_Base_Abstract {

    use RM_Admin_BreadCrumb;

    const LOGIN_ROUTE = 'admin-login';

    protected $_addRoute;
    protected $_editRoute;
    protected $_listRoute;

    protected $_addButton = true;

    /**
     * @var RM_Entity
     */
    protected $_entity;

    protected $_itemClassName;
    protected $_itemName;

    protected $_ajaxRoute;
    protected $_ajaxUrl;
    protected $_ajaxResponse;

    protected $_programmerAccessOnly = false;

    public function preDispatch() {
        parent::preDispatch();
        $this->__initParams();
        $this->__onlyAdmin();
        $this->view->assign('page', !is_null($this->_getParam('page')) ? (int)$this->_getParam('page') : 1);
        $this->__setTitle($this->_itemName);
        $this->__buildCrumbs();
    }

    protected function __initParams() {
        if ($this->_ajaxRoute) {
            $this->_ajaxUrl = $this->view->url( $this->getAllParams(), $this->_ajaxRoute);
        }
    }

    public function listAction() {
        $this->view->headTitle()->append( ucfirst($this->_listTitle) );
        if ($this->_addButton) {
            $addButton = new RM_View_Element_Button($this->_addRoute, [], $this->getAddCrumbName());
            RM_View_Top::getInstance()->addButton($addButton);
        }
    }

    public function addAction() {
        static::__configureParser();
        $this->__getCrumbs()->add($this->getAddCrumbName(), array(), $this->_addRoute);
        $this->view->headTitle()->append( ucfirst($this->_addTitle) );
        $this->view->assign('tabs', [ RM_Lang::getDefault() ]);
    }

    public function editAction() {
        static::__configureParser();
        $this->view->headTitle()->append( ucfirst($this->_editTitle) );
        $this->__getCrumbs()->add($this->getEditCrumbName(), ['id' => 0], $this->_editRoute);
        $this->view->assign( array(
            'tabs' => [ RM_Lang::getDefault() ],
            'edit' => true
        ) );
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->_entity = $this->_getItemById( $this->getParam('id') );
    }

    /**
     * @param  int $id
     * @return RM_Entity
     */
    protected function _getItemById($id) {
        return call_user_func([ $this->_itemClassName, 'getById' ], (int)$id);
    }

    public function ajaxAction() {
        $this->__disableView();
        /* @var stdClass $data */
        $data = (object)array_merge($this->getRequest()->getPost(), $this->getAllParams());
        if ( is_null($this->_ajaxResponse) ) $this->_ajaxResponse = new stdClass();
        $this->_ajaxResponse = $this->_getAjaxService()->processRequest($data);
        if (!$this->_ajaxResponse) $this->_ajaxResponse = new stdClass();
    }

    public function postDispatch() {
        parent::postDispatch();
        $this->__setViewParams();
        if ($this->_ajaxResponse instanceof stdClass || is_array($this->_ajaxResponse)) { //set ajax response
            $this->_helper->json( $this->_ajaxResponse );
        }
    }

    protected function __setViewParams() {
        $this->view->assign(array(
            'editRoute' => $this->_editRoute,
            'ajaxUrl' => $this->_ajaxUrl
        ));
    }

    protected function __onlyAdmin() {
        $this->__initSession();
        if (!$this->__hasAccess()) {
            $this->__redirectToLogin();
        }
    }

    protected function __hasAccess() {
        $hasNotProgrammerAccess = $this->_programmerAccessOnly && !$this->_user->getRole()->isProgrammer();
        return $this->__isAdmin() && !$hasNotProgrammerAccess;
    }

    protected function __redirectToLogin() {
        $this->__disableView();
        $this->redirect( $this->view->url([], self::LOGIN_ROUTE) );
    }

    protected function __getCrumbs() {
        return RM_View_Top::getInstance()->getBreadcrumbs();
    }

    protected function __buildCrumbs() {
        if (is_string( $this->_listRoute )) {
            $this->__getCrumbs()->add($this->getListCrumbName(), [], $this->_listRoute );
        }
    }

    protected function __setContentFields() {
        if (
            $this->getRequest()->isPost() &&
            $this->_entity instanceof RM_Interface_Contentable
        ) {
            $data = (object)$this->getRequest()->getPost();
            foreach ($data->lang as $idLang => $fields) {
                $lang = RM_Lang::getById( $idLang );
                $entity = $this->_entity;
                /* @var RM_Interface_Contentable $entity */
                $contentLang = $entity->getContentManager()->addContentLang($lang);
                foreach ($fields as $fieldName => $fieldContent) {
                    /* @var $contentLang RM_Content_Lang */
                    $contentLang->setFieldContent($fieldName, $fieldContent, $data->process[ $fieldName ]);
                }
            }
        }
    }

    protected function __postContentFields() {
        $_POST['lang'] = array();
        $entity = $this->_entity;
        if ($entity instanceof RM_Interface_Contentable) {
            foreach ($entity->getContentManager()->getAllContentLangs() as $contentLang) {
                $fields = array();
                foreach ($contentLang->getAllFields() as $field) {
                    $fields[ $field->getName() ] = $field->getInitialContent();
                }
                $_POST['lang'][ $contentLang->getIdLang() ] = $fields;
            }
        }
    }

    protected function _turnSwitcher($methodSuffix, $key) {
        $data = (object)$this->getRequest()->getPost();
        $prefix = (isset($data->{$key}) && intval($data->{$key}) === 1) ? 'set' : 'unset';
        call_user_func( [$this->_entity, $prefix . $methodSuffix] );
    }


    protected function __goBack() {
        $this->redirect( RM_View_Top::getInstance()->getBreadcrumbs()->getBack() );
    }

    /**
     * @return HTMLPurifier_Config
     */
    protected static function __configureParser() {
        $config = RM_Content_Field_Process_Html::init()->getCurrentConfig();
        $config->set('HTML.SafeIframe', true);
        return $config;
    }

    /**
     * @return RM_Controller_Service_Ajax
     */
    protected function _getAjaxService() {
        return new RM_Controller_Service_Ajax($this->_itemClassName );
    }

}