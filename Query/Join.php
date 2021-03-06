<?php
class RM_Query_Join
    implements
        RM_Query_Interface_ImproveSelect,
        RM_Query_Interface_Hashable {

    private $_joins = array();

    const INNER_JOIN = 1;
    const LEFT = 2;
    const RIGHT = 3;
    const CROSS = 4;
    const OUTER = 5;

    public static function get() {
        return new self();
    }

    /**
     * @param string $type
     * @param string|RM_Query_Join_Object $to
     * @param string $from
     * @param string $toKey
     * @param null|string $fromKey
     * @param array $fields
     * @return RM_Query_Join
     */
    public function add(
        $type,
        $to,
        $from,
        $toKey,
        $fromKey = null,
        array $fields = array()
    ) {
        $fromTable = is_array($from) ? key($from) : $from;
        $toTable = is_array($to) ? key($to) : $to;
        $condition = join(' = ', array(
            join('.', [$fromTable,  $fromKey ?: $toKey]),
            join('.', [$toTable, $toKey])
        ));
        $this->addWithCondition($type, $to, $from, $condition, $fields);
        return $this;
    }

    public function addWithCondition(
        $type,
        $to,
        $from,
        $condition,
        array $fields = array()
    ) {
        $key = is_array($to) ? key($to) : strval($to);
        if (isset($this->_joins[$key])) return $this;

        $this->_joins[$key] = array(
            'type' => $this->_formatJoinType($type),
            'to' => $to,
            'from' => $from,
            'joinCondition' => $condition,
            'fields' => $fields
        );
        return $this;
    }

    public function mergeWith(self $join) {
        foreach ($join->_joins as $joinData) {
            $this->addWithCondition(
                $joinData['type'],
                $joinData['to'],
                $joinData['from'],
                $joinData['joinCondition'],
                $joinData['fields']
            );
        }
    }

    public function improveQuery(Zend_Db_Select $select) {
        foreach ($this->_joins as $joinData) {
            call_user_func_array( array(
                $select,
                $this->_getJoinMethod( $joinData['type'] )
            ), array(
                $this->_extractJoinObject( $joinData['to'] ),
                $joinData['joinCondition'],
                $joinData['fields']
            ));
        }
    }

    private function _formatJoinType($type) {
        switch ($type) {
            case 'join':
            case 'inner':
            case self::INNER_JOIN:
                return self::INNER_JOIN;
                break;
            case 'left':
            case 'leftJoin':
            case 'joinLeft':
            case self::LEFT:
                return self::LEFT;
                break;
            case 'right':
            case 'rightJoin':
            case 'joinRight':
            case self::RIGHT:
                return self::RIGHT;
                break;
            case 'cross':
            case self::CROSS:
                return self::CROSS;
                break;
            case 'outer':
            case self::OUTER:
                return self::OUTER;
                break;
        }
        throw new Exception('Wrong join type given');
    }

    private function _getJoinMethod($type) {
        switch ($type) {
            case self::INNER_JOIN:
                return 'join';
            case self::LEFT:
                return 'joinLeft';
            case self::RIGHT:
                return 'joinRight';
            case self::CROSS:
                return 'joinCross';
            case self::OUTER:
                return 'joinOuter';
        }
    }

    private function _extractJoinObject($joinTo) {
        if ($joinTo instanceof RM_Query_Join_Object) {
            /* @var RM_Query_Join_Object $joinTo */
            return $joinTo->getJoinArray();
        } else {
            return $joinTo;
        }
    }

    public function isHashable() {
        return true;
    }

    public function getHash() {
        return md5(serialize($this->_joins));
    }
}