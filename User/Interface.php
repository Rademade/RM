<?php
interface RM_User_Interface
    extends
        RM_Interface_Hideable,
        RM_Interface_Deletable {

    /**
     * @abstract
     * @return int
     */
	public function getId();

	/**
	 * @abstract
	 * @return RM_User_Role
	 */
	public function getRole();

    /**
     * @abstract
     * @return RM_User_Interface
     */
    public function save();

}