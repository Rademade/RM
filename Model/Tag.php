<?php
abstract class RM_Model_Tag
    extends
        RM_Entity
    implements
        RM_Interface_Contentable,
        RM_Interface_Deletable {

    use RM_Trait_Content;
    use RM_Trait_Alias;

    const TABLE_NAME = 'rmTags';
    const CACHE_NAME = 'rmTags';

    const TAG_TYPE = '';

    protected static $_properties = array(
        'idTag' => array(
            'id'   => true,
            'type' => 'int'
        ),
        'idContent' => array(
            'type' => 'int'
        ),
        'tagAlias' => array(
            'type' => 'string'
        ),
        'tagType' => array(
            'type' => 'int'
        ),
        'tagStatus' => array(
            'type' => 'int',
            'default' => self::STATUS_UNDELETED
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
        $rmTag = new static(new RM_Compositor(array(
            'tagType' => static::TAG_TYPE
        )));
        $rmTag->setContentManager(RM_Content::create());
        return $rmTag;
    }

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where(self::TABLE_NAME . '.tagStatus != ?', self::STATUS_DELETED);
    }

    protected static function _getAliasFieldName() {
        return 'tagAlias';
    }

    public function save() {
        $this->_generateAlias();
        $this->_dataWorker->setValue('idContent', $this->getContentManager()->save()->getId());
        $this->_dataWorker->save();
        $this->__refreshAliasCache();
        $this->__refreshCache();
    }

    public function getId() {
        return $this->_dataWorker->_getKey()->getValue();
    }

    public function getIdContent() {
        return $this->_dataWorker->getValue('idContent');
    }

    protected function __setIdContent($idContent) {
        $this->_dataWorker->setValue('idContent', $idContent);
    }

    public function getName() {
        return $this->getContent()->getName();
    }

    public function getAlias() {
        return $this->_dataWorker->getValue('tagAlias');
    }

    protected function __setAlias($alias) {
        $this->_dataWorker->setValue('tagAlias', $alias);
    }

    public function getType() {
        return $this->_dataWorker->getValue('tagType');
    }

    public function getStatus() {
        return $this->_dataWorker->getValue('tagStatus');
    }

    public function setStatus($status) {
        $status = (int)$status;
        if (in_array($status, array(
            self::STATUS_DELETED,
            self::STATUS_UNDELETED
        ))) {
            $this->_dataWorker->setValue('tagStatus', $status);
        } else {
            throw new Exception('Wrong tag status');
        }
    }

    public function remove() {
        $this->setStatus(self::STATUS_DELETED);
        $this->save();
        $this->__cleanAliasCache();
        $this->__cleanCache();
    }

}