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

    public function add($type, $to, $from, $key) {
        $this->_joins[$to] = array(
            'type'  => $this->_formatJoinType( $type ),
            'to'    => $to,
            'from'  => $from,
            'key'   => $key
        );
        return $this;
    }

    public function mergeWith(self $join) {
        foreach ($join->_joins as $joinData) {
            $this->add(
                $joinData['type'],
                $joinData['to'],
                $joinData['from'],
                $joinData['key']
            );
        }
    }

    public function improveQuery(Zend_Db_Select $select) {
        foreach ($this->_joins as $joinData) {
            call_user_func_array( array(
                $select,
                $this->_getJoinMethod( $joinData['type'] )
            ), array(
                $joinData['to'],
                join(' = ', array(
                    join('.', array(
                        $joinData['from'],
                        $joinData['key']
                    )),
                    join('.', array(
                        $joinData['to'],
                        $joinData['key']
                    ))
                )),
                array()
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
            case self::LEFT:
                return self::LEFT;
                break;
            case 'right':
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

}