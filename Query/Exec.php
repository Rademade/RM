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
	 * @param array $arguments
	 * @return array
	 */
	public static function select(
		Zend_Db_Select $select,
		array $arguments
	) {
		$limits = null;
		foreach ($arguments as $argument) {
			if ($argument instanceof RM_Query_Where) {
				/* @var $argument RM_Query_Where */
				$argument->improveQuery( $select );
			}
			if ($argument instanceof RM_Query_Order) {
				/* @var $argument RM_Query_Order */
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

}