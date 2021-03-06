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
        $this->__onlyAdmin();
        $this->__initParams();
        $this->__initPageNumber();
        $this->__setTitle($this->_itemName);
        $this->__buildCrumbs();
        $this->__initModuleConst();
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
        $this->view->headTitle()->append(ucfirst($this->_addTitle));
        $this->view->assign('tabs', [RM_Lang::getDefault()]);
    }
    
    public function editAction() {
        static::__configureParser();
        $this->view->headTitle()->append( ucfirst($this->_editTitle) );
        $this->__getCrumbs()->add($this->getEditCrumbName(), ['id' => 0], $this->_editRoute);
        $this->view->assign( array(
            'tabs' => [RM_Lang::getDefault()],
            'edit' => true
        ) );
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->_entity = $this->_getItemById($this->getParam('id'));
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
        $this->redirect($this->view->url([], static::LOGIN_ROUTE));
    }
    
    protected function __getCrumbs() {
        return RM_View_Top::getInstance()->getBreadcrumbs();
    }
    
    protected function __buildCrumbs() {
        if (is_string($this->_listRoute)) {
            $this->__getCrumbs()->add($this->getListCrumbName(), [], $this->_listRoute);
        } elseif (is_array($this->_listRoute)) {
            list($routeParams, $routeName) = $this->_listRoute;
            $this->__getCrumbs()->add($this->getListCrumbName(), $routeParams, $routeName);
        }
    }
    
    protected function __setContentFields() {
        if (
            $this->getRequest()->isPost() &&
            $this->_entity instanceof RM_Interface_Contentable
        ) {
            $data = (object)$this->getRequest()->getPost();
            /* @var RM_Interface_Contentable $entity */
            $entity = $this->_entity;
            $content = $entity->getContentManager();
    
            foreach ($data->lang as $idLang => $fields) {
                $lang = RM_Lang::getById($idLang);
    
                // Content lang will be removed if field with name 'no-save' given
                if (isset($fields['no-save'])) {
                    // Load all available languages to be able remove necessary
                    $content->getAllContentLangs();
                    $content->removeContentLang($lang);
                    continue;
                }
    
                $contentLang = $content->addContentLang($lang);
    
                foreach ($fields as $fieldName => $fieldContent) {
                    /* @var $contentLang RM_Content_Lang */
                    if (isset($data->process[$fieldName])) {
                        $contentLang->setFieldContent($fieldName, $fieldContent, $data->process[$fieldName]);
                    }
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
                    $fields[ $field->getName() ] = $this->__getContentFieldValue($field);
                }
                $_POST['lang'][$contentLang->getIdLang()] = $fields;
            }
        }
    }
    
    protected function __getContentFieldValue(RM_Content_Field $field) {
        return $field->getInitialContent();
    }
    
    protected function _turnSwitcher($methodSuffix, $key) {
        $data = (object)$this->getRequest()->getPost();
        $prefix = (isset($data->{$key}) && intval($data->{$key}) === 1) ? 'set' : 'unset';
        call_user_func([$this->_entity, $prefix . $methodSuffix]);
    }
    
    
    protected function __goBack() {
        $this->redirect(RM_View_Top::getInstance()->getBreadcrumbs()->getBack());
    }
    
    /**
     * @return HTMLPurifier_Config
     */
    protected static function __configureParser() {
        $config = RM_Content_Field_Process_Html::init()->getCurrentConfig();
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(http(s)?:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
        return $config;
    }
    
    /**
     * @return RM_Controller_Service_Ajax
     */
    protected function _getAjaxService() {
        return new RM_Controller_Service_Ajax($this->_itemClassName);
    }
    
    protected function __initParams() {
        if ($this->_ajaxRoute) {
            $this->_ajaxUrl = $this->view->url($this->getAllParams(), $this->_ajaxRoute);
        }
    }
    
    protected function __initPageNumber() {
        $this->view->page = (int)$this->getParam('page') ?: 1;
    }

    protected function __initModuleConst() {
        define('APPLICATION_MODULE', $this->getParam('module'));
    }

}