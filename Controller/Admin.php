<?php
abstract class RM_Controller_Admin
    extends
        RM_Controller_Base_Abstract {

    const LOGIN_ROUTE = 'admin-login';

    protected $_listRoute;
    protected $_listTitle = 'list';

    protected $_editRoute;
    protected $_addRoute;
    protected $_addName;

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
        $this->__buildCrumbs();
        $this->__setTitle( $this->_itemName );
    }

    private function __initParams() {
        if ($this->_ajaxRoute) {
            $this->_ajaxUrl = $this->view->url(
                $this->getAllParams(),
                $this->_ajaxRoute
            );
        }
    }

    public function listAction() {
        $this->view->headTitle()->append( $this->_listTitle );
        if ($this->_addButton) {
            RM_View_Top::getInstance()->addButton(new RM_View_Element_Button(
                $this->_addRoute,
                array(),
                'Add ' .$this->_getAddName()
            ));
        }
    }

    public function addAction() {
        $action = 'Add ' . $this->_getAddName();
        $this->__getCrumbs()->add($action, array(), $this->_addRoute);
        $this->view->headTitle()->append( $action );
        $this->view->assign('tabs', [ RM_Lang::getDefault() ]);
    }

    public function editAction() {
        $action = 'Edit ' . $this->_getAddName();
        $this->view->headTitle()->append( $action );
        $this->__getCrumbs()->add($action, array(
            'id' => 0
        ), $this->_editRoute);
        $this->view->assign( array(
            'tabs' => [ RM_Lang::getDefault() ],
            'edit' => true
        ) );
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->_entity = $this->_getItemById( $this->getParam('id') );
    }

    /**
     * @param $id
     * @return RM_Entity
     */
    protected function _getItemById($id) {
        return call_user_func(
            [ $this->_itemClassName, 'getById' ],
            $id
        );
    }

    public function ajaxAction() {
        $this->__disableView();
        $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
        if ( is_null($this->_ajaxResponse) ) {
            $this->_ajaxResponse = new stdClass();
        }
        if (isset($data->type)) switch ( intval($data->type) ) {
            case RM_Interface_Deletable::ACTION_DELETE:
                $item = $this->_getItemById( $data->id );
                if ($item instanceof RM_Interface_Deletable) {
                    /* @var $item RM_Interface_Deletable */
                    $item->remove();
                    $this->_ajaxResponse->status = 1;
                }
                break;
            case RM_Interface_Hideable::ACTION_STATUS:
                $item = $this->_getItemById( $data->id );
                if ($item instanceof RM_Interface_Hideable) {
                    /* @var $item RM_Interface_Hideable */
                    switch (intval($data->status)) {
                        case RM_Interface_Hideable::STATUS_SHOW:
                            $item->show();
                            $this->_ajaxResponse->status = 1;
                            break;
                        case RM_Interface_Hideable::STATUS_HIDE:
                            $item->hide();
                            $this->_ajaxResponse->status = 1;
                            break;
                    }
                }
                break;
            case RM_Interface_Sortable::ACTION_SORT:
                foreach ($data->ids as $position => $id) {
                    $item = $this->_getItemById( $id );
                    if ($item instanceof RM_Interface_Sortable) {
                        /* @var $item RM_Interface_Sortable */
                        $item->setPosition( $position );
                        $item->save();
                    }
                }
                $this->_ajaxResponse->status = 1;
                break;
            case RM_Interface_Element::ACTION_POSITION:
                $item = $this->_getItemById( $data->id );
                if ($item instanceof RM_Interface_Sortable) {
                    /* @var $item RM_Interface_Sortable */
                    $item->setPosition( $data->position );
                    $item->save();
                }
                $this->_ajaxResponse->status = 1;
                break;
        }
    }

    public function postDispatch() {
        parent::postDispatch();
        $this->__setViewParams();
        if ($this->_ajaxResponse instanceof stdClass || is_array($this->_ajaxResponse)) { //set ajax response
            $response = $this->getResponse();
            $output = Zend_Json::encode( $this->_ajaxResponse );
            $response->setBody($output);
            $response->setHeader('content-type', 'application/json', true);
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
        if (!$this->__isAdmin()) {
            $this->__redirectToLogin();
        }
        if ($this->_programmerAccessOnly && !$this->_user->getRole()->isProgrammer()) {
            $this->__redirectToLogin();
        }
    }

    protected function __redirectToLogin() {
        $this->__disableView();
        $this->redirect(
            $this->view->url(
                array(),
                self::LOGIN_ROUTE
            )
        );
    }

    protected function __setTitle( $title ) {
        RM_View_Top::getInstance()->setTitle( $title );
        $this->view->headTitle( $title );
    }

    protected function __getCrumbs() {
        return RM_View_Top::getInstance()->getBreadcrumbs();
    }

    protected function __buildCrumbs() {
        if (is_string( $this->_listRoute )) {
            $this->__getCrumbs()->add(
                $this->_itemName . ' ' . mb_strtolower( $this->_listTitle, 'utf-8'),
                array(),
                $this->_listRoute
            );
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
                    $contentLang->setFieldContent(
                        $fieldName,
                        $fieldContent,
                        $data->process[ $fieldName ]
                    );
                }
            }
        }
    }

    protected function __postContentFields() {
        $_POST['lang'] = array();
        $entity = $this->_entity;
        if ($entity instanceof RM_Interface_Contentable) {
            /* @var RM_Interface_Contentable $entity */
            foreach ($entity->getContentManager()->getAllContentLangs() as $contentLang) {
                /* @var $contentLang RM_Content_Lang */
                $fields = array();
                foreach ($contentLang->getAllFields() as $field) {
                    /* @var $field RM_Content_Field */
                    $fields[ $field->getName() ] = $field->getInitialContent();
                }
                $_POST['lang'][$contentLang->getIdLang()] = $fields;
            }
        }
    }

    protected function _turnSwitcher($methodSuffix, $key) {
        $data = (object)$this->getRequest()->getPost();
        $prefix = (isset($data->{$key}) && intval($data->{$key}) === 1) ? 'set' : 'unset';
        call_user_func( array(
            $this->_entity,
            $prefix . $methodSuffix
        ) );
    }

    protected function _getAddName() {
        if (is_string($this->_addName)) {
            $name  = $this->_addName;
        } else {
            $name = $this->_itemName;
        }
        return mb_strtolower( $name, 'utf-8');
    }

    protected function __goBack() {
        $this->redirect(
            RM_View_Top::getInstance()->getBreadcrumbs()->getBack()
        );
    }

}