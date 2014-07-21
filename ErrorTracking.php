<?php
require_once 'ErrorTracking/Rollbar.php';

class RM_ErrorTracking {

	/**
	 * @return RollbarNotifier
	 */
	public static function initialize() {
		if (!Rollbar::$instance) {
			forward_static_call_array(['Rollbar', 'init'], static::getInitializerArgs());
		}
		return Rollbar::$instance;
	}

	public static function get() {
		return static::initialize();
	}

	public static function reportMessage() {
		return call_user_func_array([static::initialize(), 'report_message'], func_get_args());
	}

	public static function reportException() {
		return call_user_func_array([static::initialize(), 'report_exception'], func_get_args());
	}

	public static function reportFatalError() {
		return call_user_func_array([static::initialize(), 'report_fatal_error'], func_get_args());
	}

	public static function reportPhpError() {
		return call_user_func_array([static::initialize(), 'report_php_error'], func_get_args());
	}

	public static function flush() {
		return call_user_func_array([static::initialize(), 'flush'], func_get_args());
    }

	protected static function getInitializerArgs() {
		$cfg = static::getConfig();
		return [
			$cfg,
			rm_isset($cfg, 'set_exception_handler', true),
			rm_isset($cfg, 'set_error_handler', true),
			rm_isset($cfg, 'report_fatal_errors', true)
		];
	}

	protected static function getConfig() {
		$cfg = Zend_Registry::get('cfg');
		return array_merge([
			'environment' => APPLICATION_ENV,
			'root' => PUBLIC_PATH
		], rm_isset($cfg, 'error-tracking'));
	}

}