<?php
abstract class RM_Entity_ToMany_Intermediate
    extends
        RM_Entity
    implements
        RM_Interface_Deletable,
        JsonSerializable {

    /**
     * @var RM_Entity_Worker_Data
     */
    protected $_dataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;

    const FIELD_FROM = '';
    const FIELD_TO = '';
    const FIELD_STATUS = '';

    /**
     * @static
     * @param RM_Entity $from
     * @param RM_Entity $to
     * @return RM_Entity_ToMany_Intermediate
     */
    public static function create(
        RM_Entity $from,
        RM_Entity $to
    ) {
        $intermediate = static::getByBoth($from, $to);
        if (!$intermediate instanceof static) {
            $intermediate = new static( new RM_Compositor( array(
                static::FIELD_FROM => $from->getId(),
                static::FIELD_TO => $to->getId()
            ) ) );
        }
        return $intermediate;
    }

    public function destroy() {
        $this->getTo()->destroy();
        parent::destroy();
    }

    /**
     * @static
     * @param RM_Entity $from
     * @param RM_Entity $to
     * @return RM_Entity_ToMany_Intermediate
     */
    public static function getByBoth(
        RM_Entity $from,
        RM_Entity $to
    ) {
        $select = static::_getSelect();
        $select->where(static::TABLE_NAME . '.' . static::FIELD_FROM . ' = ?', $from->getId());
        $select->where(static::TABLE_NAME . '.' . static::FIELD_TO . ' = ?', $to->getId());
        return static::_initItem($select);
    }

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where(
            static::FIELD_STATUS . ' != ?', self::STATUS_DELETED
        );
    }

    /**
     * @return RM_Entity
     */
    abstract public function getFrom();

    /**
     * @return RM_Entity
     */
    abstract public function getTo();

    public function __construct(stdClass $data) {
        //TODO check if redeclared!
        //TODO check fields!
        $this->_dataWorker = new RM_Entity_Worker_Data(get_called_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_called_class());
    }

    public function getId() {
        return $this->_dataWorker->_getKey()->getValue();
    }

    public function getIdFrom() {
        return $this->_dataWorker->getValue( static::FIELD_FROM );
    }

    public function setIdFrom($id) {
        $this->_dataWorker->setValue(static::FIELD_FROM, (int)$id);
        return $this;
    }

    public function getIdTo() {
        return $this->_dataWorker->getValue( static::FIELD_TO );
    }

    public function setIdTo($id) {
        $this->_dataWorker->setValue(static::FIELD_TO, (int)$id);
        return $this;
    }

    public function getStatus() {
        return $this->_dataWorker->getValue( static::FIELD_STATUS );
    }

    public function setStatus( $status ) {
        $status = (int)$status;
        if (in_array($status, array(
            self::STATUS_DELETED,
            self::STATUS_UNDELETED
        ))) {
            $this->_dataWorker->setValue( static::FIELD_STATUS , $status);
        }
    }

    public function save() {
        $this->_dataWorker->save();
        $this->__refreshCache();
    }

    public function remove() {
        $this->setStatus( self::STATUS_DELETED );
        $this->save();
        $this->__cleanCache();
    }

    public function jsonSerialize() {
        return array(
            'idFrom' => $this->getIdFrom(),
            'idTo' => $this->getIdTo()
        );
    }
}