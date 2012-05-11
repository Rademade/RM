<?php
/**
 * @yandexMarketInterface
 */
interface RM_Yandex_Market_Item_Offer_Option_Vendor {

    /**
     * Производитель
     *
     * @abstract
     * @tag vendor
     * @return string
     */
    public function getVendor();

    /**
     * Код товара (указывается код производителя)
     *
     * @abstract
     * @tag vendorCode
     * @return string
     */
    public function getVendorCode();

    /**
     * Элемент предназначен для указания страны производства товара.
     *
     * @abstract
     * @tag country_of_origin
     * @return string
     */
    public function getOriginCountry();

    /**
     * Элемент предназначен для отметки товаров, имеющих официальную гарантию производителя.
     *
     * @abstract
     * @tag manufacturer_warranty
     * @return bool
     */
    public function hasWarranty();

}