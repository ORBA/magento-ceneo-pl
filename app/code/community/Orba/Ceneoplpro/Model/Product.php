<?php

class Orba_Ceneoplpro_Model_Product extends Mage_Catalog_Model_Product
{

    protected $categories_groups = array();
    protected $_useIds = false;
    protected $_showAttrInXmlFeed = array();

    /**
     * @var Ceneoplpro_Varien_Image
     */
    protected $_imageObj = null;

    /**
     * @return Orba_Ceneoplpro_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('ceneoplpro/config');
    }

    /**
     * @param array $additional_attributes
     * @param bool $get_invisible
     * @param array|null $filterIds
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _prepareCollection($additional_attributes, $get_invisible = false, $filterIds = null)
    {
        $storeId = $this->getConfig()->getStore()->getId();
        $this->_useIds = $this->getConfig()->shouldUseIdsForFeed($storeId);
        /** @var Mage_Catalog_Model_Resource_Product_Collection $product_collection */
        $product_collection = $this->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('ceneopro_category_id', 'left')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('ceneopro_name_sufix')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_to_date')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('ceneopro_name_pl')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect($this->getConfig()->getDescriptionAttribute($storeId))
            ->addAttributeToSelect('tax_class_id')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        if (!$get_invisible) {
            $product_collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        }
        if ($this->_showAttrInXmlFeed['weight']) {
            $product_collection->addAttributeToSelect('weight');
        }
        if (!$this->getConfig()->shouldProductsWithoutCategoryBeExported($storeId)) {
            $product_collection->addAttributeToFilter('ceneopro_category_id', array(
                'notnull' => true,
                'neq' => ''
            ));
        }

//        ->addAttributeToSelect('ceneopro_category_id', 'left')
//        ->addAttributeToFilter('entity_id', array('nin' => $product_ids))
//        ->addAttributeToFilter('ceneopro_category_id', array('notnull' => true))

//        $t4 = (string)$product_collection->getSelect();


        if (!$this->getConfig()->shouldOutOfStockProductsBeExported($storeId)) {
            /** @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $stockCollection */
            $stockCollection = Mage::getModel('cataloginventory/stock_item')->getCollection();
            $productIds = array();
            foreach ($stockCollection as $key => $item) {
                if (($item->getManageStock() && $item->getIsInStock()) || !$item->getManageStock()) {
                    $productIds[] = $item->getOrigData('product_id');
                }
                $item->clearInstance();
                unset($item);
                $stockCollection->removeItemByKey($key);
            }
            unset($stockCollection);

            if (!empty($productIds)) {
                $product_collection->addIdFilter($productIds);
            }
        }
        if ($filterIds) {
            $product_collection->addIdFilter($filterIds);
        }
        foreach ($additional_attributes as $code => $options) {
            $product_collection->addAttributeToSelect($code);
        }
        return $product_collection;
    }

    /*
     * Method that prepares one product to be appended to $offers table
     * 
     * $product - product to be processed
     * $media - object with media data
     * $rewrite_url - URL that rewrites standard product URL. If null then no rewrite is executed
     */
    protected function handleProductToGetOffer($product, $images, $mappings, $additional_attributes, $rewrite_url = null)
    {
        $storeId = $this->getConfig()->getStore()->getId();
        $conditions = $this->getConfig()->getCoreAttributesConditions($storeId, $this->_showAttrInXmlFeed);

        $_category = Mage::getSingleton('ceneoplpro/category');

        if ($this->_showAttrInXmlFeed['stock']) {
            $_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if ($_stock->getManageStock()) {
                $core_attrs['stock'] = (int)$_stock->getQty();
            }
            if ($product->getTypeId() === "configurable") {
                if (($_stock->getManageStock() && $_stock->getIsInStock())) {
                    $core_attrs['stock'] = 1;
                }
            }

            $_stock->clearInstance();
            unset($_stock);
        }
        foreach ($conditions as $attr => $data) {
            if (array_key_exists('code', $data)) {
                if (!empty($data['code']) && $product->getData($data['code']) !== null) {
                    $options = $additional_attributes[$data['code']];
                    if (empty($options)) {
                        $core_attrs[$attr] = (int)($product->getData($data['code']) == $data['value']);
                    } else {
                        $key = $product->getData($data['code']);
                        if ($key) {
                            $option = array_key_exists($key, $options) ? $options[$key] : null;
                            $core_attrs[$attr] = $option ? (int)($option == $data['value']) : 0;
                        }
                    }
                }
            } else if (array_key_exists('values', $data)) {
                if (is_array($data['values'])) {
                    foreach ($data['values'] as $value => $value_data) {
                        if (!empty($value_data['code'])) {
                            $options = $additional_attributes[$value_data['code']];
                            if (empty($options)) {
                                if ($product->getData($value_data['code']) == $value_data['value']) {
                                    $core_attrs[$attr] = $value;
                                    break;
                                }
                            } else {
                                if ($product->getData($value_data['code'])) {
                                    $option = $options[$product->getData($value_data['code'])];
                                    if ($option == $value_data['value']) {
                                        $core_attrs[$attr] = $value;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!isset($core_attrs[$attr]) && isset($data['default'])) {
                    $core_attrs[$attr] = $data['default'];
                }
            }
        }
        unset($conditions);
        $ceneopro_category_id = $product->getCeneoproCategoryId();
        if (!isset($this->categories_groups[$ceneopro_category_id])) {
            if ($ceneopro_category_id) {
                $this->categories_groups[$ceneopro_category_id] = $_category->load($ceneopro_category_id)->getGroup();
            } else {
                $this->categories_groups[$ceneopro_category_id] = 'other';
            }
        }
        $group = $this->categories_groups[$ceneopro_category_id];
        $group_attrs = array();

        foreach ($mappings[$group] as $attr => $mapping) {
            if (!empty($mapping)) {
                if ($product->getTypeId() === "configurable" && ($this->isAttributeSuperAttribute($mapping, $product))) {
                    $value = $this->getChildrenAttrValues($product->getId(), $mapping);
                    if (!empty($value)) {
                        $group_attrs[$attr] = $value;
                    }
                } else {
                    $value = $product->getData($mapping);
                    if (!empty($value)) {
                        $options = $additional_attributes[$mapping];
                        if (!empty($options)) {
                            $group_attrs[$attr] = $options[$value];
                        } else {
                            $group_attrs[$attr] = $value;
                        }
                    }
                }
            }
        }

        $imgs = array();
        $i = 0;
        foreach ($images as $image) {
            if (isset($image['url'])) {
                $imgs[] = $image['url'];
            } else {
                $imgs[] = $this->_getImageUrl($image['file']);
            }
            if ($i == 1) {
                break;
            }
            $i++;
        }
        $cat = $ceneopro_category_id ? $_category->getPathArray($ceneopro_category_id) : null;
        $price = $this->getFinalPriceIncludingTax($product);
        if (!$price) {
            return null;
        }

        if (is_null($rewrite_url)) {
            $product_url = $product->getProductUrl();
        } else {
            $product_url = $rewrite_url;
        }

        $offer = array(
            'group' => $group,
            'record' => array(
                'id' => $this->_useIds ? $product->getId() : $product->getSku(),
                'url' => $product_url,
                'price' => $price,
                'name' => $product->getName() . $product->getCeneoproNameSufix(),
                'namePl' => $product->getCeneoproNamePL(),
                'desc' => $product->getData($this->getConfig()->getDescriptionAttribute($storeId)),
                'imgs' => $imgs,
                'cat' => $cat,
                'group_attrs' => $group_attrs,
                'core_attrs' => $core_attrs
            )
        );
        if ($this->_showAttrInXmlFeed['weight']) {
            $offer['record']['weight'] = $product->getWeight();
        }

        return $offer;
    }

    protected function isAttributeSuperAttribute($attributeName, $product)
    {
        $superAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        if (is_array($superAttributes)) {
            foreach ($superAttributes as $superAttribute) {
                if (isset($superAttribute['attribute_code'])) {
                    if ($attributeName == $superAttribute['attribute_code']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    protected function _setShowAttrArray($storeId = null)
    {
        $attrs = array('avail', 'weight', 'set', 'basket', 'stock');
        foreach ($attrs as $attr) {
            $this->_showAttrInXmlFeed[$attr] = $this->getConfig()->shouldShowInXmlFeed($attr, $storeId);
        }
    }

    /*
     * Getting offers for generating XML file
     */
    public function getOffers()
    {
        $this->_setShowAttrArray();
        $additional_attributes = array();
        $_attribute = Mage::getModel('ceneoplpro/attribute');
        $storeId = $this->getConfig()->getStore()->getId();
        $this->_setShowAttrArray($storeId);
        $mappings = $this->getConfig()->getAttributesMappings($storeId, $this->_showAttrInXmlFeed);
        foreach ($mappings as $group) {
            foreach ($group as $mapping) {
                if (!empty($mapping)) {
                    if (!in_array($mapping, $additional_attributes)) {
                        $additional_attributes[$mapping] = $_attribute->getOptionsByCode($mapping);
                    }
                }
            }
        }
        $product_collection = $this->_prepareCollection($additional_attributes, true);
        $media = $this->getMediaData($product_collection);

        $offers = array();

//        $t = (string)$product_collection->getSelect();
        foreach ($product_collection as $key => $product) {

            if ($this->getConfig()->exportConfigurableProducts($storeId)) {

                $images = isset($media[$product->getId()]) ? $media[$product->getId()] : array();
                // Do not show simple product in XML if it's invisible
                if ($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                    $product->clearInstance();
                    unset($product);
                    $product_collection->removeItemByKey($key);
                    continue;
                }
                $product_url = null;
                $offer[] = $this->handleProductToGetOffer($product, $images, $mappings, $additional_attributes, $product_url);
            } else {

                // Do not show configurable products in XML
                if ($product->getTypeId() === "configurable") {
                    continue;
                } else {
                    $images = isset($media[$product->getId()]) ? $media[$product->getId()] : array();
                    $configurable_id = $this->getConfigurableIdByChild($product);
                    if ($configurable_id !== 0) {
                        // Do not show simple product in XML if its parent is invisible
                        $configurable = $product_collection->getItemById($configurable_id);
                        if (!$configurable || $configurable->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                            $product->clearInstance();
                            unset($product);
                            $product_collection->removeItemByKey($key);
                            continue;
                        }
                        if (empty($images) && isset($media[$configurable_id])) {
                            $images = $media[$configurable_id];
                        }
                        $product_url = $configurable->getProductUrl();
                    } else {
                        // Do not show simple product in XML if it's invisible
                        if ($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                            $product->clearInstance();
                            unset($product);
                            $product_collection->removeItemByKey($key);
                            continue;
                        }
                        $product_url = null;
                    }
                    $offer[] = $this->handleProductToGetOffer($product, $images, $mappings, $additional_attributes, $product_url);
                }
            }

            //append results to main array
            for ($i = 0; $i < count($offer); $i++) {
                if (!is_null($offer[$i])) {
                    $group = $offer[$i]['group'];
                    $val = $offer[$i]['record'];
                    $offers[$group][] = $val;
                }
            }

            unset($offer); //clear after appending
            $product->clearInstance();
            unset($product);
            $product_collection->removeItemByKey($key);
        }
        // GC for configurable products
        foreach ($product_collection as $key => $product) {
            $product->clearInstance();
            unset($product);
            $product_collection->removeItemByKey($key);
        }
        unset($product_collection);
        return $offers;
    }

    public function getMediaData($_productCollection)
    {
        $all_ids = $_productCollection->getAllIds();
        if ($this->getConfig()->isWatermark()) {
            return $this->_getImagesWithWatermark($all_ids);
        } else {
            return $this->_getSimpleImages($all_ids);
        }
    }

    protected function _getSimpleImages($_productIds)
    {
        $all_ids = $_productIds;
        $_mediaGalleryByProductId = array();
        if (!empty($all_ids)) {
            $_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
            $_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');

            $_mediaGalleryData = $_read->fetchAll('
				SELECT
					main.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
					`value`.`label`, `value`.`position`, `value`.`disabled`, `default_value`.`label` AS `label_default`,
					`default_value`.`position` AS `position_default`,
					`default_value`.`disabled` AS `disabled_default`
				FROM `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery') . '` AS `main`
					LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `value`
						ON main.value_id=value.value_id AND value.store_id=' . Mage::app()->getStore()->getId() . '
					LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `default_value`
						ON main.value_id=default_value.value_id AND default_value.store_id=0
				WHERE (
					main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
					AND (main.entity_id IN (' . $_read->quote($all_ids) . '))
				ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
			');
            foreach ($_mediaGalleryData as $_galleryImage) {
                $k = $_galleryImage['entity_id'];
                unset($_galleryImage['entity_id']);
                if (!isset($_mediaGalleryByProductId[$k])) {
                    $_mediaGalleryByProductId[$k] = array();
                }
                $_mediaGalleryByProductId[$k][] = $_galleryImage;
            }
            unset($_mediaGalleryData);
        }

        return $_mediaGalleryByProductId;
    }

    protected function _getImagesWithWatermark($productIds)
    {
        $_mediaGalleryByProductId = array();
        foreach ($productIds as $id) {
            $product = Mage::getModel('catalog/product')->load($id);
            /* @var $product Mage_Catalog_Model_Product */
            $imageType = $this->getConfig()->getImageType();
            $imageSize = $this->getConfig()->getImageSize();
            $url = (string)Mage::helper('catalog/image')->init($product, $imageType)->resize($imageSize);
            $_mediaGalleryByProductId[$product->getId()][] = array('url' => $url);
        }
        return $_mediaGalleryByProductId;
    }

    public function updateCeneoCategory($product_ids = array(), $ceneopro_category_id, $storeId)
    {
        $error = false;
        try {
            $product_collection = $this->getCollection()->setStoreId($storeId)
                ->addAttributeToSelect('ceneopro_category_id', 'left')
                ->addAttributeToFilter('entity_id', array('in' => $product_ids));
            foreach ($product_collection as $key => $product) {
                $product->setCeneoproCategoryId($ceneopro_category_id);
                $product->setStoreId($storeId);
                $this->getResource()->saveAttribute($product, 'ceneopro_category_id');
            }
        } catch (Exception $e) {
            $error = true;
            Mage::getModel('adminhtml/session')->addException($e, Mage::helper('ceneoplpro')->__('An error occurred while running this mapping.'));
        }
        return !$error;
    }


    public function clearOldMaping($product_ids, $storeId)
    {
        if (empty($product_ids)){
            $product_ids[] = "";
        }
        try {
            $product_collection = $this->getCollection()->setStoreId($storeId)
                ->addAttributeToSelect('ceneopro_category_id', 'left')
                ->addAttributeToFilter('entity_id', array('nin' => $product_ids))
                ->addAttributeToFilter('ceneopro_category_id', array('notnull' => true))
            ;
            foreach ($product_collection as $key => $product) {
                $product->setCeneoproCategoryId(null);
                $product->setStoreId($storeId);
                $product->getResource()->saveAttribute($product, 'ceneopro_category_id');
            }
        } catch (Exception $e) {
            Mage::getModel('adminhtml/session')->addException($e, Mage::helper('ceneoplpro')->__('An error occurred while running this mapping.'));
            return false;
        }
        return true;
    }

    public function clearAllOldMaping()
    {

        try {
            $product_collection = $this->getCollection()
                ->addAttributeToSelect('ceneopro_category_id', 'left')
            ;
            foreach ($product_collection as $product) {
                $product->setCeneoproCategoryId(null);
                $product->getResource()->saveAttribute($product, 'ceneopro_category_id');
            }
        } catch (Exception $e) {
            Mage::getModel('adminhtml/session')->addException($e, Mage::helper('ceneoplpro')->__('An error occurred while running this mapping.'));
            return false;
        }
        return true;
    }


    // TODO: Remove memory leak
    public function getFinalPriceIncludingTax($product)
    {
        return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
    }

    /**
     * Gets id of a configurable product for specified child product
     *
     * @param Mage_Catalog_Model_Product $child
     * @return int
     */
    public function getConfigurableIdByChild($child)
    {
        $ids = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($child->getId());
        if (!empty($ids)) {
            return (int)array_shift($ids);
        }
        return 0;
    }


    /**
     * @param $parentProductId
     * @param null $attributeCode
     * @return array|bool
     */
    public function getChildrenAttrValues($parentProductId, $attributeCode = null)
    {
        $storeId = $this->getConfig()->getStore()->getId();
        $childProductArray = Mage::getSingleton('catalog/product_type_configurable')->getChildrenIds($parentProductId);

        if (!isset($childProductArray[0])) {
            return false;
        }
        if (!$attributeCode) {
            return false;
        }

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect($attributeCode);
        $collection->addAttributeToFilter('status', 1);
        $collection->addAttributeToFilter('entity_id', array('in' => $childProductArray[0]));

        $array = array();
        foreach ($collection as $item) {
            if ($item->getData($attributeCode)) {
                if (!$this->getConfig()->shouldOutOfStockProductsBeExported($storeId)) {
                    $stocklevel = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item);
                    if (($stocklevel->getManageStock() && $stocklevel->getIsInStock()) || !$stocklevel->getManageStock()) {
                        $array[] = $item->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($item);
                    }
                } else {
                    $array[] = $item->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($item);
                }
            }
        }
        $array = array_unique($array);
        $values = implode(";", $array);
        return $values;
    }


    /*
     *
     */
    public function regenerateFeeds()
    {
        $stores = Mage::getModel('core/store')->getCollection();
        foreach ($stores as $store) {
            $url = $store->getUrl('ceneoplpro/products/feed', array('hash' => $this->getConfig()->getHash($store->getId()), 'live' => '1'));
            $res = $this->_fetchUrl($url);
        }
        return true;
    }

    protected function _fetchUrl($url)
    {
        try {
            if (extension_loaded('curl') && function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec') && function_exists('curl_close')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                Mage::log('Fetched ' . $url . ' (' . strlen($data) . ') with CURL', null, 'ceneoplpro.log');
                if ($error) {
                    Mage::log('CURL error: ' . $error, null, 'ceneoplpro.log');
                } else {
                    return $data;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 3600
            )
        ));
        $data = @file_get_contents($url, false, $ctx);
        Mage::log('Fetched ' . $url . ' (' . strlen($data) . ') with file_get_contents', null, 'ceneoplpro.log');
        return $data;
    }

    /**
     * Resize image if it's too big (Ceneo accepts max 2000 x 2000 px) and returns its URL.
     *
     * @param string $imageFile
     * @return string
     */
    protected function _getImageUrl($imageFile)
    {
        $imageDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "catalog" . DS . "product";
        $imageResizedDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DS . "catalog" . DS . "product" . DS . "ceneoplpro";
        $imagesBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
        if (is_null($this->_imageObj)) {
            $this->_imageObj = new Ceneoplpro_Varien_Image();
        }
        if (file_exists($imageDir . $imageFile)) {
            try {
                $this->_imageObj->reload($imageDir . $imageFile);
                $width = $this->_imageObj->getOriginalWidth();
                $height = $this->_imageObj->getOriginalHeight();
                if ($width > 2000 || $height > 2000) {
                    if (!file_exists($imageResizedDir . $imageFile)) {
                        $this->_imageObj->constrainOnly(true);
                        $this->_imageObj->keepAspectRatio(true);
                        $this->_imageObj->keepFrame(false);
                        $this->_imageObj->resize(2000, 2000);
                        $this->_imageObj->save($imageResizedDir . $imageFile);
                        Mage::log('Image resized for Ceneo feed: ' . $imageFile, null, 'ceneoplpro.log');
                    }
                    $imagesBaseUrl .= '/ceneoplpro';
                }
            } catch (Exception $e) {
                Mage::log('Problem with image file (' . $e->getMessage() . '): ' . $imageFile, null, 'ceneoplpro.log');
            }
        }
        return $imagesBaseUrl . $imageFile;
    }

}