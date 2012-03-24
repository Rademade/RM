<?php
/**
* @property mixed adminAccess
* @property mixed programmerAccess
* @property mixed userAccess
* @property mixed shortDesc
* @property mixed hierarchy
* @property mixed idRole
*/
class RM_User_Role
	extends
		RM_Entity {

	const CACHE_NAME = 'role';

	const TABLE_NAME = 'roles';

	protected static $_properties = array(
		'idRole' => array(
			'id' => true,
			'type' => 'int'
		),
		'shortDesc' => array(
			'type' => 'string'
		),
		'adminAccess' => array(
			'type' => 'int'
		),
		'userAccess' => array(
			'type' => 'int'
		),
		'programmerAccess' => array(
			'type' => 'int'
		),
		'hierarchy' => array(
			'type' => 'int'
		)
	);

	const ADMIN_ACCESS_BASE = 1;
	const ADMIN_ACCESS_COUNRTY_MANGER = 2;
	const ADMIN_ACCESS_SIMPLE_MANAGER = 3;
	
	const USER_ACCESS_BASE = 1;
	const USER_ACCESS_BASE_ROLE_ID = 5;

	public function getShortDesc() {
		return $this->shortDesc;
	}
	
	public function getHierarchy() {
		return $this->hierarchy;
	}

	public function isSubordinate(RM_User_Role $role) {
		return ($this->getHierarchy() < $role->getHierarchy());
	}

	public function isAdmin() {
		return in_array($this->adminAccess, array(
			self::ADMIN_ACCESS_BASE,
			self::ADMIN_ACCESS_COUNRTY_MANGER,
			self::ADMIN_ACCESS_SIMPLE_MANAGER
		));
	}
	
	public function isProgrammer() {
		return ($this->programmerAccess === 1);	
	}
	
	public function isMainAdmin() {
		return ($this->adminAccess === self::ADMIN_ACCESS_BASE);
	}

	public function isCountryManager() {
		return ($this->adminAccess === self::ADMIN_ACCESS_COUNRTY_MANGER) || $this->isMainAdmin();
	}
	
	public function isSimpleManager() {
		return ($this->adminAccess === self::ADMIN_ACCESS_COUNRTY_MANGER) || $this->isCountryManager();	
	}
	
	public function isUser() {
		return ($this->userAccess === self::USER_ACCESS_BASE) || $this->isSimpleManager();
	}

	public function getDesc() {
		return $this->shortDesc;
	}

}