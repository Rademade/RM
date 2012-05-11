<?php
class RM_Yandex_Market_Document {

    private $_xml;

    private $_shop;

    public function __construct(array $shopOptions) {
        $date = date('Y-m-d H:i');
        $this->_xml = new SimpleXMLElement(join('', array(
            '<?xml version="1.0" encoding="utf-8"?>',
            '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">',
            '<yml_catalog date="' . $date . '">',
            '</yml_catalog>'
        )));
        $this->_shop = $this->_xml->addChild('shop');
        $this->_generateShopData( $shopOptions['shop'] );
    }

    /**
     * @param array $shopOptions
     */
    private function _generateShopData(array $shopOptions) {
        foreach ($shopOptions as $key => $name) {
            $this->_shop->{$key} = $name;
        }
    }

    /**
     * @param RM_Yandex_Market_Item_Currency[] $currencies
     * @throws Exception
     */
    public function setCurrencies(array $currencies) {
        $currenciesElement = $this->_shop->addChild('currencies');
        foreach ($currencies as $currency) {
            if ($currency instanceof RM_Yandex_Market_Item_Currency) {
                /* @var RM_Yandex_Market_Item_Currency $currency */
                $currencyElement = $currenciesElement->addChild('currency');
                $currencyElement->addAttribute('id', $currency->getCode());
                $currencyElement->addAttribute('rate', $currency->getRate());
            } else {
                throw new Exception('Wrong $currencies array given');
            }
        }
    }

    /**
     * @param RM_Yandex_Market_Item_Category[] $categories
     */
    public function setCategories(array $categories) {
        $categoriesElement = $this->_shop->addChild('categories');
        foreach ($categories as $category) {
            if ($category instanceof RM_Yandex_Market_Item_Category) {
                /* @var RM_Yandex_Market_Item_Category $category */
                $categoryElement = $categoriesElement->addChild('category', $category->getName() );
                $categoryElement->addAttribute('id', $category->getId());
                if ($category->getIdParent() !== 0) {
                    $categoryElement->addAttribute('parentId', $category->getIdParent());
                }
            }
        }
    }

    public function setDeliveryPrice($price) {
        $this->_shop->addChild('local_delivery_cost', $price);
    }

    /**
     * @param RM_Yandex_Market_Item_Offer[] $offers
     */
    public function setOffers(array $offers) {
        $offerProcessor = new RM_Yandex_Market_Item_Offer_Processor(get_class($offers[0]));
        $offersElement = $this->_shop->addChild('offers');
        foreach ($offers as $offer) {
            /* @var RM_Yandex_Market_Item_Offer $offer */
            if ($offer instanceof RM_Yandex_Market_Item_Offer && $offer->getIdCategory()) {
                $offerElement = $offersElement->addChild('offer');
                $offerProcessor->setParams($offer, $offerElement);
            }
        }
    }

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->_xml->asXML();
    }


}