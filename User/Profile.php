<?php
/**
 * @property int idUser
 * @property string profilePassword
 * @property int profileStatus
 * @property string profileEmail
 * @property string profileLastname
 * @property string profileName
 */
class RM_User_Profile
    extends
        RM_Entity
    implements
        RM_User_Profile_Interface,
        RM_Entity_Extendable {

    const TABLE_NAME = 'rmProfiles';

    const CACHE_NAME = 'rmProfiles';

    const PASSWORD_SALT = 'rmPasswd->Super.Salt';

    /**
     * @var RM_User_Interface
     */
    private $_user;

    /**
     * @var RM_Entity_Worker_Data
     */
    private $_dataWorker;

    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;

    protected static $_properties = array(
        'idUser' => array(
            'id' => true,
            'ai' => false,
            'type' => 'int'
        ),
        'profileName' => array(
            'type' => 'string'
        ),
        'profileLastname' => array(
            'type' => 'string'
        ),
        'profileEmail' => array(
            'type' => 'string'
        ),
        'profilePassword' => array(
            'type' => 'string'
        ),
        'profileStatus' => array(
            'type' => 'int',
            'default' => self::STATUS_HIDE
        )
    );

    public function __construct($data) {
        $this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }

    public function getId() {
        return $this->__get(
            static::_getKeyAttributeProperties()->getName()
        );
    }

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where('profileStatus != ?', self::STATUS_DELETED);
        $select->join(
            RM_User_Base::TABLE_NAME,
            join(' = ', array(
                RM_User_Base::TABLE_NAME . '.idUser',
                self::TABLE_NAME . '.idUser',
            ))
        );
        RM_User_Base::_setSelectRules( $select );
    }

    public function __get($name) {
        $val = $this->_dataWorker->getValue($name);
        if (is_null($val)) {
            throw new Exception("Try to get unexpected attribute {$name}");
        } else {
            return $val;
        }
    }

    public function __set($name, $value) {
        if (is_null($this->_dataWorker->setValue($name, $value))) {
            throw new Exception("Try to set unexpected attribute {$name}");
        }
    }

    public static function getByUser(RM_User_Interface $user) {
        return static::getById( $user->getId() );
    }

    public static function getByEmail($email) {
        $select = self::_getSelect();
        $select->where('profileEmail = ?', $email);
        return self::_initItem( $select );
    }

    public function getIdUser() {
        return $this->getId();
    }

    protected function __setUser(RM_User_Base $user) {
        $this->idUser = $user->getId();
        $this->_user = $user;
    }

    /**
     * @return RM_User_Interface
     */
    public function getUser() {
        if (!$this->_user instanceof RM_User_Interface) {
            $model = RM_Dependencies::getInstance()->userClass;
            /** @var $_user RM_User_Base */
            $this->_user = $model::getById( $this->getIdUser() );
        }
        return $this->_user;
    }

    public function validate() {
        if ($this->getName() == '') {
            throw new Exception('User name is empty');
        }
        if ($this->getLastname() == '') {
            throw new Exception('User lastname is empty');
        }
        if ($this->getEmail() == '') {
            throw new Exception('User email is empty');
        }
    }

    private function _createUser() {
        if ($this->getIdUser() === 0) {
            $userModel = RM_Dependencies::getInstance()->userClass;
            $this->__setUser( new $userModel() );
        }
    }

    public function __cachePrepare() {
        $this->_user = null;
    }

    public function save() {
        $this->validate();
        $this->_createUser();
        $this->getUser()->save();
        $this->_dataWorker->setValue('idUser', $this->getUser()->getId());
        if ($this->_dataWorker->save()) {
            $this->__refreshCache();
        }
    }

    private function _validateEmail(
        $email,
        RM_Exception $e = null,
        $isThrow
    ) {
        if (is_null($e))
            $e = new RM_Exception();
        $validator = new RM_User_Validation_Email( $email );
        $validator->format();
        if (!$validator->isValid()) {
            $e[] = 'Email not valid';
        } else {
            if (!$validator->isUnique( $this->getId() )) {
                $e[] = 'Such email already exists';
            }
        }
        if ($isThrow && (bool)$e->current())
            throw $e;
        return $validator->getEmail();
    }

    public function setEmail($email) {
        if ($this->getEmail() !== $email) {
            $email = $this->_validateEmail($email, null, true );
            $this->profileEmail = $email;
        }
    }

    public function getEmail() {
        return $this->profileEmail;
    }

    public function setName($name) {
        $name = trim( $name );
        if ($name == '') {
            throw new Exception('User name is empty');
        }
        if ($this->getName() !== $name) {
            if (strlen($name) > 250) {
                throw new Exception('Wrong user name. Name length must be below 250 chars');
            }
            $this->profileName = $this->_getLineProcessor()->getParsedContent( $name );
        }
    }

    public function getName() {
        return $this->profileName;
    }

    public function setLastname($lastname) {
        $lastname = trim( $lastname );
        if ($lastname == '') {
            throw new Exception('User lastname is empty');
        }
        if ($this->getLastname() !== $lastname) {
            if (strlen($lastname) > 250) {
                throw new Exception('Wrong user lastname. Name length must be below 250 chars');
            }
            $this->profileLastname = $this->_getLineProcessor()->getParsedContent( $lastname );
        }
    }

    public function getLastname() {
        return $this->profileLastname;
    }

    public function getFullName() {
        return $this->getName() . ' ' . $this->getLastname();
    }

    public function setPassword($password) {
        if ($password === false) {//Empty password
            $this->profilePassword = '';
        } else {
            if (!$this->checkPassword($password)) {
                $length = strlen($password);
                if ($length < 6) {
                    throw new Exception('Very short password');
                }
                if ($length > 50) {
                    throw new Exception('Overlong password');
                }
                $this->profilePassword = $this->_generatePasswordHash( trim( $password ) );
            }
        }
    }

    private function _generatePasswordHash($password) {
        return sha1( md5( $password ) . static::PASSWORD_SALT);
    }

    public function checkPassword($password) {
        return $this->profilePassword == $this->_generatePasswordHash( $password );
    }

    public function getStatus() {
        return $this->profileStatus;
    }

    public function setStatus($status) {
        if (in_array($status, array(
            self::STATUS_DELETED,
            self::STATUS_HIDE,
            self::STATUS_SHOW,
            self::STATUS_UNDELETED
        ))) {
            $this->profileStatus = $status;
        } else {
            throw new Exception('Wrong status given');
        }
    }

    public function isShow() {
        return $this->getUser()->isShow();
    }

    public function show() {
        $this->getUser()->show();
        $this->setStatus( self::STATUS_SHOW );
        $this->save();
    }

    public function hide() {
        $this->getUser()->hide();
        $this->setStatus( self::STATUS_HIDE );
        $this->save();
    }

    public function remove() {
        $this->getUser()->remove();
        $this->setStatus( self::STATUS_DELETED );
        $this->save();
        $this->__cleanCache();
    }

    /**
     * @return RM_Content_Field_Process_Line
     */
    private function _getLineProcessor() {
        return RM_Content_Field_Process_Line::init();
    }

    public function __toArray() {
        return array(
            'idUser' => $this->getId(),
            'profileName' => $this->getName(),
            'profileLastname' => $this->getLastname(),
            'profileEmail' => $this->getEmail()
        );
    }

}
