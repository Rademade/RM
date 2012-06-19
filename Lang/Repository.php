<?php
class RM_Lang_Repository
    extends
        RM_Search_Repository {

    const CLASS_NAME = 'RM_Lang';

    public static function getActiveLangs() {
        $search = new RM_Lang_Search();
        $search->onlyShow();
        return call_user_func_array(
            array($search, 'getResults'),
            func_get_args()
        );
    }

}