<?php
interface RM_Interface_Deletable {

    const ACTION_DELETE = 2;

    const STATUS_DELETED = 0;
    const STATUS_UNDELETED = 1;

    public function remove();

}