<?php
/**
 * @yandexMarketInterface
 */
interface RM_Yandex_Market_Item_Offer_Options_Model {

    /**
     * Группа товаров \ категория
     *
     * @abstract
     * @tag typePrefix
     * @return string
     */
    public function getTypePrefix();


    /**
     * Модель
     *
     * @abstract
     * @tag model
     * @return string
     */
    public function getModel();

    /**
     * Штрихкод товара, указанный производителем.
     *
     * @abstract
     * @tag barcode
     * @return string
     */
    public function getBarcode();

}