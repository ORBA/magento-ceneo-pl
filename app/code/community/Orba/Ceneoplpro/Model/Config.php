<?php

class Orba_Ceneoplpro_Model_Config extends Mage_Core_Model_Abstract
{

    const CACHE_TAG = 'CENEOPLPRO';
    const AVAIL_DEFAULT_VALUE = 99;
    const CATEGORIES_XML_URL = 'http://api.ceneo.pl/Kategorie/dane.xml';

    public static $groups = array(
        'core' => array('avail', 'set', 'basket'),
        'books' => array('Autor', 'ISBN', 'Ilosc_stron', 'Wydawnictwo', 'Rok_wydania', 'Oprawa', 'Format', 'dynamic'),
        'tires' => array('Producent', 'SAP', 'EAN', 'Model', 'Szerokosc_opony', 'Profil', 'Srednica_kola', 'Indeks_predkosc', 'Indeks_nosnosc', 'Sezon', 'dynamic'),
        'rims' => array('Producent', 'Kod_producenta', 'EAN', 'Rozmiar', 'Rozstaw_srub', 'Odsadzenie', 'dynamic'),
        'perfumes' => array('Producent', 'Kod_producenta', 'EAN', 'Linia', 'Rodzaj', 'Pojemnosc', 'dynamic'),
        'music' => array('Wykonawca', 'EAN', 'Nosnik', 'Wytwornia', 'Gatunek', 'dynamic'),
        'games' => array('Producent', 'Kod_producenta', 'EAN', 'Platforma', 'Gatunek', 'dynamic'),
        'movies' => array('Rezyser', 'EAN', 'Nosnik', 'Wytwornia', 'Obsada', 'Tytul_oryginalny', 'dynamic'),
        'medicines' => array('Producent', 'BLOZ_12', 'Ilosc', 'dynamic'),
        'grocery' => array('Producent', 'EAN', 'Ilosc', 'dynamic'),
        'clothes' => array('Producent', 'Model', 'EAN', 'Kolor', 'Rozmiar', 'Kod_producenta', 'Sezon', 'Fason', 'ProductSetId', 'dynamic'),
        'other' => array('Producent', 'Kod_producenta', 'EAN', 'Pojemnosc', 'dynamic')
    );

    public function getGroups()
    {
        return self::$groups;
    }

    public function getCoreAttributes()
    {
        return self::$groups['core'];
    }

    public function getCoreAttributesConditions($storeId = null, $filters = array())
    {
        $attributes = $this->getCoreAttributes();
        $res = array();
        foreach ($attributes as $attr) {
            if (isset($filters[$attr]) && !$filters[$attr]) {
                continue;
            }
            $res[$attr] = $this->getAttributeConditions($attr, 'core', $storeId);
        }
        return $res;
    }

    public function getAttributeConditions($attr, $group, $storeId = null)
    {
        if ($group == 'core' && $attr == 'avail') {
            $values = array(1, 3, 7, 14);
            $res = array(
                'values' => array(),
                'default' => self::AVAIL_DEFAULT_VALUE
            );
            foreach ($values as $value) {
                $res['values'][$value] = array(
                    'code' => Mage::getStoreConfig('ceneoplpro/attr_core/avail_' . $value . '_name', $storeId),
                    'value' => Mage::getStoreConfig('ceneoplpro/attr_core/avail_' . $value . '_value', $storeId)
                );
            }
            return $res;
        } else {
            return array(
                'code' => Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr . '_name', $storeId),
                'value' => Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr . '_value', $storeId)
            );
        }
    }

    public function getAttributesMappings($storeId = null, $filters = array())
    {
        $groups = $this->getGroups();
        $res = array();
        foreach ($groups as $group => $attributes) {
            $res[$group] = array();
            foreach ($attributes as $attr) {
                if ($group != 'core') {
                    $res[$group][$attr] = Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr, $storeId);
                    if ($attr == 'dynamic') {
                        $arrayDynamic = $this->dynamicAttrArray($group, $attr, $storeId);
                        if (!empty($arrayDynamic)) {
                            $res[$group] = array_merge($res[$group], $arrayDynamic[$group]);
                        }
                        unset($res[$group][$attr]);
                    }
                } else {
                    if (isset($filters[$attr]) && !$filters[$attr]) {
                        continue;
                    }
                    if ($attr != 'avail') {
                        $res[$group][$attr] = Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr . '_name', $storeId);
                    } else {
                        $indexes = array(1, 3, 7, 14);
                        foreach ($indexes as $i) {
                            $res[$group][$attr . '_' . $i] = Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr . '_' . $i . '_name', $storeId);
                        }
                    }
                }
            }
        }
        return $res;
    }


    public function dynamicAttrArray($group, $attr, $storeId)
    {
        $res = [];
        $attributeArray = Mage::getStoreConfig('ceneoplpro/attr_' . $group . '/' . $attr, $storeId);
        if ($attributeArray) {
            $decodedValue = Mage::helper('core/unserializeArray')->unserialize($attributeArray);

            if (is_array($decodedValue)) {
                foreach ($decodedValue as $_val) {
                    if (isset($_val['magento_attr_id']) && isset($_val['ceneo_attr_name'])) {
                        $magentoAttrId = trim($_val['magento_attr_id']);
                        $ceneoAttrName = trim($_val['ceneo_attr_name']);
                        $res[$group][$ceneoAttrName] = $magentoAttrId;
                    }
                }
            }
        }
        return $res;
    }


    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $serializedValue = $this->getValue();
            $unserializedValue = false;
            if (!empty($serializedValue)) {
                try {
                    $unserializedValue = Mage::helper('core/unserializeArray')->unserialize($serializedValue);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
            $this->setValue($unserializedValue);
        }
    }


    public function getPriceIncludesTax($storeId = null)
    {
        return Mage::getStoreConfig('tax/calculation/price_includes_tax', $storeId);
    }

    public function getStore()
    {
        return Mage::app()->getStore();
    }

    public function saveHash()
    {
        $hash = md5(microtime());
        Mage::getModel('core/config')->saveConfig('ceneoplpro/config/hash', $hash, 'default', 0);
    }

    public function getHash($storeId = null)
    {
        return Mage::getStoreConfig('ceneoplpro/config/hash', $storeId);
    }

    public function getCategoriesXmlUrl()
    {
        return self::CATEGORIES_XML_URL;
    }

    public function isFlatCatalogEnabled()
    {
        return Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
    }

    public function isWatermark()
    {
        return (bool)Mage::getStoreConfig('ceneoplpro/config/watermark');
    }

    public function getImageType()
    {
        return Mage::getStoreConfig('ceneoplpro/config/image_option');
    }

    public function getImageSize()
    {
        return Mage::getStoreConfig('ceneoplpro/config/image_size');
    }

    public function getDescriptionAttribute($storeId = null)
    {
        return Mage::getStoreConfig('ceneoplpro/attr_core/description', $storeId);
    }

    public function shouldProductsWithoutCategoryBeExported($storeId = null)
    {
        return Mage::getStoreConfig('ceneoplpro/config/export_also_products_without_categories', $storeId);
    }

    public function shouldOutOfStockProductsBeExported($storeId = null)
    {
        return Mage::getStoreConfigFlag('ceneoplpro/config/export_out_of_stock_products', $storeId);
    }

    public function shouldUseIdsForFeed($storeId = null)
    {
        return Mage::getStoreConfig('ceneoplpro/config/use_ids', $storeId);
    }

    public function shouldShowInXmlFeed($attr, $storeId = null)
    {
        return Mage::getStoreConfig('ceneoplpro/config/xml_show_' . $attr, $storeId);
    }

    public function isFeedLive()
    {
        return Mage::getStoreConfig('ceneoplpro/config/live');
    }

    public function exportConfigurableProducts($storeId = null)
    {
        return Mage::getStoreConfigFlag('ceneoplpro/config/export_configurable_products', $storeId);
    }

}