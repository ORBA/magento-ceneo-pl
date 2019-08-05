<?php

class Orba_Ceneoplpro_Model_Mapping extends Mage_Rule_Model_Rule
{

    protected $_conditions;
    protected $_productIds;
    protected $_product;

    protected function _construct()
    {
        $this->_init('ceneoplpro/mapping');
    }

    protected function getConfig()
    {
        return Mage::getSingleton('ceneoplpro/config');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('ceneoplpro/mapping_condition_combine');
    }

    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }
        return $this->_conditions;
    }

    public function afterLoad()
    {
        $this->_afterLoad();
        parent::afterLoad();
    }

    protected function _afterLoad()
    {
        $conditions_arr = unserialize($this->getConditionsSerialized());
        if (!empty($conditions_arr) && is_array($conditions_arr)) {
            $this->getConditions()->loadArray($conditions_arr);
        }
    }

    protected function _beforeSave()
    {
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }
        if ($this->getCeneoproCategoryId() == '') {
            $this->setCeneoproCategoryId(null);
        }
    }

    public function getMatchingProductIds()
    {
        $this->_productIds = array();
        $this->setCollectedAttributes(array());
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $this->getConditions()->collectValidatedAttributes($productCollection);
        Mage::getSingleton('core/resource_iterator')->walk(
            $productCollection->getSelect(), array(array($this, 'callbackValidateProduct')), array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
            )
        );
        unset($productCollection);
        return $this->_productIds;
    }


    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $results = array();
        foreach ($this->getStoreId() as $key => $storeId) {
            $product->setStoreId($storeId);
            $results[$storeId] = (int)$this->getConditions()->validate($product);
        }
        $this->_productIds[$product->getId()] = $results;
        unset($product);
        unset($args);
    }


    protected function productIdsByStoreId($productIds, $storeId)
    {
        $array = array();
        foreach ($productIds as $key => $value) {
            if (isset($value[$storeId])) {
                if ($value[$storeId]) {
                    $array[] = $key;
                }
            }
        }

        return $array;
    }


    public function run()
    {
        ini_set('max_execution_time', 0);
        $this->load();
        $storeIds = $this->getStoreId();
        if (!$storeIds) {
            $storeIds[] = $this->getDefaultStoreId();
            $this->setStoreId($storeIds);
        }
        if ($this->getId()) {
            $matched_product_ids = $this->getMatchingProductIds();
            $_product = Mage::getModel('ceneoplpro/product');

            if ($matched_product_ids) {
                foreach ($storeIds as $storeId) {
                    $matched_product_ids_by_store = $this->productIdsByStoreId($matched_product_ids, $storeId);
                    if ($matched_product_ids_by_store) {
                        $_product->updateCeneoCategory($matched_product_ids_by_store, $this->getCeneoproCategoryId(), $storeId);
                    }
                }
                return $matched_product_ids;
            }
        }
        return false;
    }

    public function getDefaultStoreId()
    {
        $websites = Mage::getModel('core/website')->getCollection()->addFieldToFilter('is_default', 1);
        $defaultStoreId = $websites->getFirstItem()->getDefaultGroup()->getDefaultStoreId();
        return $defaultStoreId;
    }

}