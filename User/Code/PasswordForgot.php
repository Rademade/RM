<?php
/**
* @property int idCode
* @property string newPassword
*/
class RM_User_Code_PasswordForgot
    extends
        RM_User_Code {
    
    /**
     * @var RM_Entity_Worker_Data
     */
    private $_entityWorker;
    
    const TABLE_NAME = 'forgotPasswords';
    
    protected static $_properties = array(
        'idForgotCode' => array(
            'id' => true,
            'type' => 'int'
        ),
        'idCode' => array(
            'type' => 'int'
        ),
        'newPassword' => array(
            'type' => 'string'
        )
    );
    
    public function __construct($data) {
        parent::__construct($data);
        $this->_entityWorker = new RM_Entity_Worker_Data(get_class(), $data);
    }
    
    public function __get($name) {
        $val = $this->_entityWorker->getValue($name);
        return (is_null($val)) ? parent::__get($name) : $val;
    }
    
    public function __set($name, $value) {
        if (is_null($this->_entityWorker->setValue($name, $value))) {
            parent::__set($name, $value);
        }
    }
    
    public static function create($user) {
        $code = parent::create($user);
        /* @var $code RM_User_Code_PasswordForgot */
        $code->_generatePassword();
        return $code;
    }
    
    public static function getMyType() {
        return self::TYPE_FORGOT_PASSWORD;
    }
    
    private function _generatePassword() {
        $this->newPassword = self::__generate( rand(6, 10) );
    }
    
    public function getPassword() {
        return $this->newPassword;
    }
    
    public static function _setSelectRules(Zend_Db_Select $select) {
        parent::_setSelectRules( $select );
        $select->join(
            RM_User_Code::TABLE_NAME,
            RM_User_Code::TABLE_NAME .  '.idCode = ' . self::TABLE_NAME . '.idCode',
            RM_User_Code::_getDbAttributes()
        );
        parent::_setSelectRules( $select );
    }
    
    public function save() {
        parent::save();
        $this->idCode = parent::getId();
        $this->_entityWorker->save();
    }

}