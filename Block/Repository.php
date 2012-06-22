<?php
class RM_Block_Repository
    extends
        RM_Search_Repository {

    const CLASS_NAME = 'RM_Block';

    protected static function __getSearch() {
        $search = new RM_Block_Search();
        return $search;
    }

    public static function getShowedBlocks($idPage, $searchType) {
        $search = static::__getSearch();
        $search->onlyShowStatus();
        $search->setIdPage($idPage);
        $search->setSearchType($searchType);
        return call_user_func_array(
            array($search, 'getResults'),
            func_get_args()
        );
    }

    public static function getBlocksTypeOf( $searchType ) {
        $search = static::__getSearch();
        $search->onlyShowStatus();
        $search->setSearchType($searchType);
        return call_user_func_array(
            array($search, 'getResults'),
            func_get_args()
        );
    }

}
