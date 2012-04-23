<?php
interface RM_User_Interface {

	public function getId();

	/**
	 * @abstract
	 * @return RM_User_Role
	 */
	public function getRole();

}