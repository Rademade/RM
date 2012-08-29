<?php
interface RM_Interface_Gallarizable {

    public function getIdGallery();

    /**
     * TODO check other ptojects
     * @abstract
     * @return RM_Gallery
     */
    public function getGallery();

    public function getGallarizableItemId();

    public function getGallarizableItemType();

}