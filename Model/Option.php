<?php
class RM_Model_Option
    extends
        RM_Entity
    implements
        RM_Interface_Contentable {

    use RM_Trait_Content;

    const TABLE_NAME = 'rmOptions';
    const CACHE_NAME = 'rmOptions';

    protected static $_properties = array(
        'idOption' => array(
            'type' => 'int',
            'id' => true
        ),
        'idContent' => array(
            'type' => 'int'
        ),
        'optionKey' => array(
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

    public function __construct(stdClass $data) {
        $this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }

    public static function create() {
        $option = new self(new RM_Compositor(array()));
        $option->setContentManager(RM_Content::create());
        return $option;
    }

    public static function getValueByKey($key) {
        $value = self::getCacher()->load($key);
        if (is_null($value)) {
            $option = self::getByKey($key);
            if (!$option instanceof self) {
                throw new Exception('Option with key "' . $key . '" does not exist');
            }
            $value = $option->getValue();
            self::getCacher()->cache($value, $key);
        }
        return $value;
    }

    /**
     * @param $key
     * @return RM_Model_Option
     */
    public static function getByKey($key) {
        return self::findOne(array(
            'optionKey' => $key
        ));
    }

    public function save() {
        $this->_dataWorker->setValue('idContent', $this->getContentManager()->save()->getId());
        $this->_dataWorker->save();
        $this->__refreshCache();
    }

    public function __refreshCache() {
        parent::__refreshCache();
        self::getCacher()->cache($this->getValue(), $this->getKey());
    }

    public function getId() {
        return $this->_dataWorker->_getKey()->getValue();
    }

    public function getIdContent() {
        return $this->_dataWorker->getValue('idContent');
    }

    public function __setIdContent($idContent) {
        $this->_dataWorker->setValue('idContent', $idContent);
    }

    public function setValue($value) {
        $this->getContent()->setValue($value);
    }

    public function getValue() {
        return $this->getContent()->getValue();
    }

    public function setName($name) {
        $this->getContent()->setName($name);
    }

    public function getKey() {
        return $this->_dataWorker->getValue('optionKey');
    }

    public function setKey($optionKey) {
        if ($this->_isUniqueKey($optionKey)) {
            $this->_dataWorker->setValue('optionKey', $optionKey);
        } else {
            throw new Exception('Опция с таким ключем уже существует');
        }
    }

    private function _isUniqueKey($key) {
        $option = self::getByKey($key);
        return !$option instanceof self || $option->getId() == $this->getId();

    }

}