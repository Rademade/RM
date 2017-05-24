<?php
class RM_Content_Field_Name
    extends
        RM_Entity {
    
    const CACHE_NAME = 'fieldsNames';
    
    const TABLE_NAME = 'fieldsNames';
    
    protected static $_properties = array(
        'idFieldName' => array(
            'id' => true,
            'type' => 'int'
        ),
        'fieldName' => array(
            'type' => 'string'
        )
    );
    
    /**
     * @var RM_Entity_Worker_Data
     */
    private $_dataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;
    
    private static function create($name) {
        $fieldName = new self( new RM_Compositor() );
        $fieldName->setName($name);
        $fieldName->save();
        return $fieldName;
    }
    
    public function __construct(stdClass $data) {
        $this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }
    
    public function getId() {
        return $this->_dataWorker->getValue('idFieldName');
    }
    
    public function getName() {
        return $this->_dataWorker->getValue('fieldName');
    }
    
    public function __refreshCache() {
        parent::__refreshCache();
        $where = new RM_Query_Where();
        $where->add('idFieldName', RM_Query_Where::EXACTLY, $this->getId());
        foreach (RM_Content_Field::getList($where) as $field) {
            /* @var $field RM_Content_Field */
            $field->__refreshCache();
        }
    }
    
    protected function __cache() {
        parent::__cache( );
        $this->__cacheEntity( md5( $this->getName() ) );
    }
    
    public function setName($fieldName) {
        if (!$fieldName)
            throw new Exception('Empty field name given');
        if ($this->getName() !== $fieldName) {
            if (self::getByName($fieldName, false))
                throw new Exception('Such name already exist');
            $fieldName = mb_strtolower( trim($fieldName) );
            $this->_dataWorker->setValue('fieldName', $fieldName);
        }
    }
    
    public function save() {
        $this->_dataWorker->save();
        $this->__refreshCache();
    }
    
    public static function getByName($name, $create = true) {
        $key = md5($name);
        if (is_null($filedName = self::_getStorage()->getData($key))) {
            if (is_null($filedName = self::__load($key))) {
                $select = self::_getSelect();
                $select->where('fieldName = ?', $name);
                $filedName = self::_initItem($select);
                if (is_null($filedName) && $create) {
                    $filedName = self::create($name);
                } elseif ($filedName instanceof self) {
                    $filedName->__cache();
                }
            }
            self::_getStorage()->setData($filedName, $key);
        }
        return $filedName;
    }

}