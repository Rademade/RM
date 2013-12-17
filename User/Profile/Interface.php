<?php
interface RM_User_Profile_Interface
    extends
        RM_Interface_Hideable,
        RM_Interface_Deletable {

    /**
     * @return RM_User_Interface
     */
    public function getUser();

    public function getEmail();

    public function getFullName();

    public function setPassword($password);

    public function checkPassword($password);

}