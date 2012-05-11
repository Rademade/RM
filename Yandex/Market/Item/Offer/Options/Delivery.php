<?php
/**
 * @yandexMarketInterface
 */
interface RM_Yandex_Market_Item_Offer_Option_Delivery {

    /**
     * Элемент, обозначающий возможность доставить соответствующий товар. "false" данный товар не может быть доставлен.
     *
     * @abstract
     * @tag delivery
     * @return bool
     */
    public function isDelivery();

    /**
     * Стоимость доставки данного товара в Своем регионе
     *
     * @abstract
     * @tag local_delivery_cost
     * @return float
     */
    public function getDeliveryCost();

}