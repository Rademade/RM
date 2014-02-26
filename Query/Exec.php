<?php
class RM_Query_Exec {

	public static function getHash(array $arguments) {
		$key = '';
		foreach ($arguments as $argument) {
			/* @var $argument RM_Query_Interface_Hashable */
			if (!$argument->isHashable()) {
				return false;
			}
			$hash = $argument->getHash();
			if ($hash !== false) {
				$key .= ($hash . '_');
			}
		}
		return $key;
	}

	/**
	 * @static
	 * @param Zend_Db_Select $select
	 * @param RM_Query_Interface_ImproveSelect[] $arguments
	 * @return array
	 */
	public static function select(
		Zend_Db_Select $select,
		array $arguments
	) {
		$limits = null;
		foreach ($arguments as $argument) {
			if ($argument instanceof RM_Query_Interface_ImproveSelect) {
                /* @var RM_Query_Interface_ImproveSelect $argument */
				$argument->improveQuery( $select );
			}
			if ($argument instanceof RM_Query_Limits) {
				$limits = $argument;
			}
		}
		if (is_null($limits)) {
			$limits = new RM_Query_Limits( 0 );
		}
		return $limits->getResult( $select );
	}

    public static function getRowCount(Zend_Db_Select $select, $idFieldName) {
        $db = Zend_Registry::get('db');
        //RM_TODO refactoring
        /* @var  Zend_Db_Adapter_Abstract $db */
        $sqlQuery = preg_replace(
            '/^(SELECT)(.*?)(FROM)/i',
            '$1 COUNT(' . $idFieldName . ') as count $3',
            $select->assemble()
        );
        $resultRow = $db->fetchRow( $sqlQuery );
        return $resultRow ? (int)$resultRow->count : 0;
    }

}