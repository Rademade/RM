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
        RM_Entity
    implements
        RM_Interface_Roleable {
    
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
    const ADMIN_ACCESS_COUNTRY_MANAGER = 2;
    const ADMIN_ACCESS_SIMPLE_MANAGER = 3;
    
    const USER_ACCESS_BASE = 1;
    const USER_ACCESS_BASE_ROLE_ID = 5;
    
    const HIERARCHY_PROGRAMMER = 1;
    const HIERARCHY_MAIN_ADMIN = 2;
    const HIERARCHY_COUNTRY_MANAGER = 3;
    const HIERARCHY_SIMPLE_MANAGER = 4;
    const HIERARCHY_SIMPLE_USER = 5;
    
    public function getShortDesc() {
        return $this->shortDesc;
    }
    
    public function getHierarchy() {
        return $this->hierarchy;
    }
    
    /**
     * @param \RM_Interface_Roleable|\RM_User_Role $role
     * @return bool
     */
    public function isSubordinate(RM_Interface_Roleable $role) {
        return ($this->getHierarchy() < $role->getHierarchy());
    }
    
    public function isHigherRoleThen(RM_User_Role $role) {
        return $this->getHierarchy() > $role->getHierarchy();
    }
    
    public function isAdmin() {
        return in_array($this->adminAccess, array(
            self::ADMIN_ACCESS_BASE,
            self::ADMIN_ACCESS_COUNTRY_MANAGER,
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
        return ($this->adminAccess === self::ADMIN_ACCESS_COUNTRY_MANAGER) || $this->isMainAdmin();
    }
    
    public function isSimpleManager() {
        return ($this->adminAccess === self::ADMIN_ACCESS_SIMPLE_MANAGER) || $this->isCountryManager();
    }
    
    public function isUser() {
        return ($this->userAccess === self::USER_ACCESS_BASE) || $this->isSimpleManager();
    }
    
    public function getDesc() {
        return $this->shortDesc;
    }
    
    public function hasAccess($accessLevel) {
        return $this->getHierarchy() <= $accessLevel;
    }

}