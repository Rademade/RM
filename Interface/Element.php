<?php
/**
 * Class RM_Interface_Element
 * @deprecated
 */
interface RM_Interface_Element {

//    const ACTION_STATUS = 1;
//    const ACTION_DELETE = 2;
//    const ACTION_ADD_PHOTO = 3;
//    const ACTION_SORT = 4;
//    const ACTION_SEARCH = 5;
    const ACTION_ADD = 6;
    const ACTION_SAVE = 7;
    
    const STATUS_DELETED = 0;
    const STATUS_SHOW = 1;
    const STATUS_HIDE = 2;
    const STATUS_BLOCKED = 3;

    const ACTION_POSITION = 8;

}