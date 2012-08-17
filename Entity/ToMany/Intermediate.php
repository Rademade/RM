<?php
abstract class RM_Entity_ToMany_Intermediate
    extends
        RM_Entity
    implements
        RM_Interface_Deletable {

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
        $select->where(static::FIELD_FROM . ' = ?', $from->getId());
        $select->where(static::FIELD_TO . ' = ?', $to->getId());
        return static::_initItem($select);
    }

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where(
            static::FIELD_STATUS . ' != ?', self::STATUS_DELETED
        );
    }

    abstract public function getFrom();

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
    }

    public function getIdTo() {
        return $this->_dataWorker->getValue( static::FIELD_TO );
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

}