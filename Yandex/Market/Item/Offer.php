<?php
/**
 * @yandexMarketInterface
 */
interface RM_Yandex_Market_Item_Offer {

    /**
     * @attribute id
     * @abstract
     * @return int
     */
    public function getID();

    /**
     * @attribute cid
     * @abstract
     * @return int
     */
    public function getCID();

    /**
     * @attribute cbid
     * @abstract
     * @return int
     */
    public function getCBID();

    /**
     * URL-адрес страницы товара. Максимальная длина URL — 255 символов.
     *
     * @abstract
     * @tag url
     * @return string
     */
    public function getUrl();

    /**
     * Статус доступности товара — в наличии/на заказ
     *
     * @abstract
     * @attribute available
     * @return bool
     */
    public function isAvalible();

    /**
     * Цена, по которой данный товар можно приобрести.
     * Цена товарного предложения округляеся и выводится в зависимости от настроек пользователя.
     *
     * @abstract
     * @tag price
     * @return float
     */
    public function getPrice();

    /**
     * Идентификатор валюты товара (RUR, USD, UAH, KZT).
     *
     * @abstract
     * @tag currencyId
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Идентификатор категории товара (целое число не более 18 знаков).
     * Товарное предложение может принадлежать только одной категории
     *
     * @abstract
     * @tag categoryId
     * @return int
     */
    public function getIdCategory();

    /**
     * Ссылка на картинку соответствующего товарного предложения.
     * Недопустимо давать ссылку на "заглушку", т.е. на картинку где написано "картинка отсутствует" или на логотип магазина
     *
     * @abstract
     * @tag picture
     * @return array
     */
    public function getPosterPath();

    /**
     * null | vendor.model | book | audiobook | artist.title | tour | ticket | event-ticket
     *
     * @abstract
     * @attribute type
     * @return string
     */
    public function getYandexMarketType();

    /**
     * Элемент описывает возможность приобрести товар в точке продаж без предварительного заказа по интернету.
     * Если для данного товара предусмотрена такая возможность, используется значение "true".
     * В противном случае — "false".
     *
     * @abstract
     * @tag store
     * @return bool
     */
    public function isStore();

    /**
     * Элемент характеризует наличие самовывоза (возможность предварительно заказать товар и забрать его в точке продаж).
     * Если предусмотрен самовывоз данного товара, используется значение "true". В противном случае — "false".
     *
     * @abstract
     * @tag pickup
     * @return bool
     */
    public function isPickup();


    /**
     * Элемент предназначен для обозначения товара, который можно скачать.
     *
     * @abstract
     * @tag downloadable
     * @return bool
     */
    public function getDownloadable();

    /**
     * @abstract
     * @tag sales_notes
     * @return string
     */
    public function getNote();

}