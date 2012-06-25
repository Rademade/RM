<?php
/**
 * @property int idUser
 * @property int userStatus
 * @property int idRole
 */
class RM_User_Base
    extends
        RM_Entity
    implements
        RM_User_Interface {

    const TABLE_NAME = 'rmUsers';

    const CACHE_NAME = 'rmUsers';

    /**
     * @var RM_User_Role
     */
    private $_role;

    protected static $_properties = array(
        'idUser' => array(
            'id' => true,
            'type' => 'int'
        ),
        'idRole' => array(
            'default' => RM_User_Role::USER_ACCESS_BASE_ROLE_ID,
            'type' => 'int'
        ),
        'userStatus' => array(
            'default' => self::STATUS_SHOW,
            'type' => 'int'
        )
    );

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->join(
            RM_User_Role::TABLE_NAME,
            join(' = ', array(
                RM_User_Role::TABLE_NAME . '.idRole',
                self::TABLE_NAME . '.idRole',
            ))
        );
        $select->where('userStatus != ?', self::STATUS_DELETED);
    }

    public function getIdRole() {
        return $this->idRole;
    }

    public function getStatus() {
        return $this->userStatus;
    }

    public function setStatus($status) {
        if (in_array($status, array(
            self::STATUS_DELETED,
            self::STATUS_HIDE,
            self::STATUS_SHOW,
            self::STATUS_UNDELETED
        ))) {
            $this->userStatus = $status;
        } else {
            throw new Exception('Wrong status given');
        }
    }

    public function remove() {
        $this->setStatus( self::STATUS_DELETED );
        $this->save();
    }

    public function isShow() {
        return $this->getStatus() === self::STATUS_SHOW;
    }

    public function show() {
        $this->setStatus( self::STATUS_SHOW );
        $this->save();
    }

    public function hide() {
        $this->setStatus( self::STATUS_HIDE );
        $this->save();
    }

    public function save() {
        parent::save();
        return $this;
    }

    /**
     * @return RM_User_Role
     */
    public function getRole() {
        if (!$this->_role instanceof RM_User_Role) {
            $this->_role = RM_User_Role::getById( $this->getIdRole() );
        }
        return $this->_role;
    }

    public function setRole(RM_User_Role $role) {
        $this->_role = $role;
        $this->idRole = $role->getId();
    }

}