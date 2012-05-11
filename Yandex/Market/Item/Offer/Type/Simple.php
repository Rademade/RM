<?php
/**
 * @yandexMarketInterface
 */
interface RM_Yandex_Market_Item_Offer_Type_Simple {

    /**
     * Наименование товарного предложения
     *
     * @abstract
     * @tag name
     * @return string
     */
    public function getName();

    /**
     * Описание товарного предложения
     *
     * @abstract
     * @tag description
     * @return string
     */
    public function getDescription();

}