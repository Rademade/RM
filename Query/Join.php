<?php
class RM_Query_Join
    implements
        RM_Query_Interface_ImproveSelect {

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
        if ( isset($this->_joins[ strval( $to ) ]) ) {
            return $this;
        }
        $this->_joins[ strval($to) ] = array(
            'type'      => $this->_formatJoinType( $type ),
            'to'        => $to,                                     //joining table name
            'from'      => $from,                                   //base table name
            'toKey'     => $toKey,                                  //joining table key
            'fromKey'   => is_null($fromKey) ? $toKey : $fromKey,    //base table key
            'fields'    => $fields
        );
        return $this;
    }

    public function mergeWith(self $join) {
        foreach ($join->_joins as $joinData) {
            $this->add(
                $joinData['type'],
                $joinData['to'],
                $joinData['from'],
                $joinData['toKey'],
                $joinData['fromKey'],
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
                join(' = ', array(
                    join('.', array(
                        $joinData['from'],
                        $joinData['fromKey']
                    )),
                    join('.', array(
                        $joinData['to'],
                        $joinData['toKey']
                    ))
                )),
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

}