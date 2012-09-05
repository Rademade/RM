<?php
class RM_Lang_Repository
    extends
        RM_Search_Repository {

    const CLASS_NAME = 'RM_Lang';

    private static $_activeLangs;

    /**
     * @static
     * @return RM_Lang[]
     */
    public static function getActiveLangs() {
        if (!is_array(self::$_activeLangs)) {
            $search = new RM_Lang_Search();
            $search->onlyShow();
            self::$_activeLangs = call_user_func_array(
                array($search, 'getResults'),
                func_get_args()
            );
        }
        return self::$_activeLangs;
    }

}