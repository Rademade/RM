<?php
abstract class RM_System_Controller_Admin
	extends Zend_Controller_Action {

	const LOGIN_ROUTE = 'admin-login';

	/**
	 * @var RM_User_Session
	 */
	protected $_session;
	/**
	 * @var RM_User
	 */
	protected $_user;

	protected $_listRoute;
	protected $_listTitle = 'list';

	protected $_editRoute;
	protected $_addRoute;
	protected $_addName;

	protected $_addButton = true;

	protected $_entity;

	protected $_itemClassName;
	protected $_itemName;

	protected $_ajaxResponse;

	public function preDispatch() {
		$this->__onlyAdmin();
		$this->view->assign('page', !is_null($this->_getParam('page')) ? (int)$this->_getParam('page') : 1);
		$this->__buildCrumbs();
		$this->__setTitle( $this->_itemName );
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
		$this->view->tabs = array(
			RM_Lang::getDefault()
		);
	}

	public function editAction() {
		$action = 'Edit ' . $this->_getAddName();
		$this->view->headTitle()->append( $action );
		$this->__getCrumbs()->add($action, array(), $this->_editRoute);
		$this->view->tabs = array(
			RM_Lang::getDefault()
		);
		$this->_helper->viewRenderer->setScriptAction('add');
		$this->_entity = call_user_func(
			array( $this->_itemClassName, 'getById' ),
			$this->_getParam('id')
		);
	}

	public function ajaxAction() {
		$this->__disableView();
		$data = (object)array_merge($this->getRequest()->getPost(), $_GET);
		$this->_ajaxResponse = new stdClass();
		switch ( intval($data->type) ) {
			case RM_Interface_Deletable::ACTION_DELETE:
				$item = call_user_func(
					array( $this->_itemClassName, 'getById' ),
					$data->id
				);
				if ($item instanceof RM_Interface_Deletable) {
					$item->remove();
					$this->_ajaxResponse->status = 1;
				}
				break;
			case RM_Interface_Hideable::ACTION_STATUS:
				$item = call_user_func(
					array( $this->_itemClassName, 'getById' ),
					$data->id
				);
				if ($item instanceof RM_Interface_Hideable) {
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
					$item = call_user_func(
						array( $this->_itemClassName, 'getById' ),
						$id
					);
					if ($item instanceof RM_Interface_Sortable) {
						$item->setPosition( $position );
						$item->save();
					}
				}
				$this->_ajaxResponse->status = 1;
				break;
		}
	}

	public function postDispatch(){
		$this->view->user = $this->_user;
		if ($this->_ajaxResponse instanceof stdClass) { //set ajax response
			$response = $this->getResponse();
			$output = Zend_Json::encode( $this->_ajaxResponse );
			$response->setBody($output);
			$response->setHeader('content-type', 'application/json', true);
		}
	}

	protected function __initSession() {
		if (!($this->_session instanceof RM_User_Session)) {
			$this->_session = RM_User_Session::getInstance();
			$this->_user = $this->_session->getUser();
		}
	}

	protected function __isAdmin() {
		$this->__initSession();
	    return $this->_user instanceof RM_User && $this->_user->getRole()->isAdmin();
	}

	protected function __onlyAdmin() {
		$this->__initSession();
		if (!$this->__isAdmin())
			$this->__redirectToLogin();
	}

	protected function __redirectToLogin() {
		$this->__disableView();
		return $this->_redirect( $this->view->url(array(), self::LOGIN_ROUTE));
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

	protected function __disableView() {
		$this->_helper->layout()->disableLayout(true);
		$this->_helper->viewRenderer->setNoRender(true);
	}

	protected function __setContentFields() {
		if (
			$this->getRequest()->isPost() &&
			$this->_entity instanceof RM_Interface_Contentable
		) {
			$data = (object)$this->getRequest()->getPost();
			foreach ($data->lang as $idLang => $fields) {
				$lang = RM_Lang::getById( $idLang );
				$contentLang = $this->_entity->getContentManager()->addContentLang($lang);
				foreach ($fields as $fieldName => $fieldContent) {
					/* @var $contentLang RM_Content_Lang */
					$contentLang->setFieldContent($fieldName, $fieldContent, $data->process[ $fieldName ]);
				}
			}
		}
	}

	protected function __postContentFields() {
		$_POST['lang'] = array();
		if ($this->_entity instanceof RM_Interface_Contentable) {
			foreach ($this->_entity->getContentManager()->getAllContentLangs() as $contentLang) {
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

	private function _getAddName() {
		if (is_string($this->_addName)) {
			$name  = $this->_addName;
		} else {
			$name = $this->_itemName;
		}
		return mb_strtolower( $name, 'utf-8');
	}

}