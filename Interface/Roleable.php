<?php
interface RM_Interface_Roleable {

    public function hasAccess($accessLevel);
    public function isSubordinate(RM_Interface_Roleable $roleable);
    public function isAdmin();
    public function isProgrammer();

}