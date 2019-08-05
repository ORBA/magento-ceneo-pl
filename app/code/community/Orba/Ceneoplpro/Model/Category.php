<?php

class Orba_Ceneoplpro_Model_Category extends Mage_Core_Model_Abstract
{

    private $externalIds = null;
    private $importRes = null;


    function addValueSortToCollection($collection, $dir = Varien_Data_Collection::SORT_ORDER_ASC)
    {

        $attribute = $this->getAttribute()->getAttributeCode();
        if ($attribute) {
            $ceneo_categories = Mage::getModel('ceneoplpro/category')->toOptionHash();
            if ($dir == Varien_Data_Collection::SORT_ORDER_ASC) {
                asort($ceneo_categories, SORT_NATURAL);
            } else {
                arsort($ceneo_categories, SORT_NATURAL);
            }

            $ids = implode(', ', array_keys($ceneo_categories));
            $collection->getSelect()->order(new Zend_Db_Expr("$attribute IS NULL, FIELD($attribute, $ids)"));
        }
        return $collection;
    }

    protected function _construct()
    {
        $this->_init('ceneoplpro/category');
    }

    protected function getConfig()
    {
        return Mage::getSingleton('ceneoplpro/config');
    }

    public function getAllOptions($flat = true, $empty = true)
    {
        if ($flat) {
            $cache_id = 'ceneoplpro_categories_option_array_flat';
            if (false !== ($data = Mage::app()->getCache()->load($cache_id))) {
                $options = unserialize($data);
            } else {
                $options = $this->getFlatTree();
                Mage::app()->getCache()->save(serialize($options), $cache_id, array(Orba_Ceneoplpro_Model_Config::CACHE_TAG));
            }
            if ($empty) {
                $options = array_merge(array(array('label' => '', 'value' => '')), $options);
            }
        } else {
            $cache_id = 'ceneoplpro_categories_option_array_tree';
            if (false !== ($data = Mage::app()->getCache()->load($cache_id))) {
                $options = unserialize($data);
            } else {
                $options = $this->getTree();
                Mage::app()->getCache()->save(serialize($options), $cache_id, array(Orba_Ceneoplpro_Model_Config::CACHE_TAG));
            }
        }
        return $options;
    }

    public function getFlatTree()
    {
        $res = array();
        $categories = $this->getCollection()
            ->setOrder('path', 'asc');
        foreach ($categories as $id => $category) {
            $res = $this->_getFlatTreeStep($id, $category, $res);
        }
        asort($res);
        $flatTree = array();
        foreach ($res as $id => $namePath) {
            $flatTree[] = array(
                'label' => ($categories->getItemById($id)->getIsDeleted() ? '[' . Mage::helper('ceneoplpro')->__('Deleted') . '] ' : '') . $namePath,
                'value' => $id
            );
        }
        unset($res);
        foreach ($categories as $key => $category) {
            $category->clearInstance();
            unset($category);
            $categories->removeItemByKey($key);
        }
        unset($categories);
        return $flatTree;
    }

    protected function _getFlatTreeStep($id, $category, $res)
    {
        $parentId = $category->getParentId();
        if ($parentId) {
            $res[$id] = $res[$parentId] . ' / ' . $category->getName();
        } else {
            $res[$id] = $category->getName();
        }
        return $res;
    }

    public function getTree($parent = null)
    {
        $res = array();
        $parent_id = ($parent === null) ? 0 : $parent->getId();
        $category_collection = $this->getCollection()
            ->addFieldToFilter('parent_id', $parent_id)
            ->setOrder('name', 'asc');
        foreach ($category_collection as $key => $category) {
            if ($parent === null) {
                $category->setNamePath($category->getName());
            } else {
                $category->setNamePath($parent->getNamePath() . ' / ' . $category->getName());
            }
            $res[$category->getId()] = array(
                'label' => ($category->getIsDeleted() ? '[' . Mage::helper('ceneoplpro')->__('Deleted') . '] ' : '') . $category->getNamePath(),
                'value' => $category->getId(),
                'children' => $this->getTree($category)
            );
            $category->clearInstance();
            unset($category);
            $category_collection->removeItemByKey($key);
        }
        return $res;
    }

    public function getPathArray($id)
    {
        $this->load($id);
        $name = $this->getName();
        if ($this->getParentId()) {
            return array_merge($this->getPathArray($this->getParentId()), array($name));
        } else {
            return array($name);
        }
    }

    public function getChildrenIds($id, $tree = null)
    {
        $res = array();
        if ($tree === null) {
            $tree = $this->getAllOptions(false);
        }
        if (isset($tree[$id])) {
            foreach ($tree[$id]['children'] as $child_id => $child) {
                $res[] = $child_id;
                $res = array_merge($res, $this->getChildrenIds($child_id, $tree[$id]['children']));
            }
        } else {
            foreach ($tree as $child) {
                $res = array_merge($res, $this->getChildrenIds($id, $child['children']));
            }
        }
        return $res;
    }

    public function toOptionHash($empty = true)
    {
        $e = $empty ? array(array('label' => Mage::helper('ceneoplpro')->__('not set'), 'value' => 'null')) : array();
        $options = array_merge($e, $this->getAllOptions(true, false));
        $option_hash = array();
        foreach ($options as $option) {
            $option_hash[$option['value']] = $option['label'];
        }
        return $option_hash;
    }

    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned' => true,
            'default' => null,
            'extra' => null
        );
        $helper = Mage::helper('core');
        if (!method_exists($helper, 'useDbCompatibleMode') || $helper->useDbCompatibleMode()) {
            $column['type'] = 'int';
            $column['is_null'] = true;
        } else {
            $column['type'] = Varien_Db_Ddl_Table::TYPE_INTEGER;
            $column['nullable'] = true;
        }
        return array($attributeCode => $column);
    }

    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }

    public function doImport()
    {
        $url = $this->getConfig()->getCategoriesXmlUrl();
        $error = false;
        try {
            $xml = simplexml_load_file($url);
            $this->externalIds = array();
            $this->importRes = array(
                'created' => 0,
                'updated' => 0,
                'deleted' => 0
            );
            $this->parseAndSave($xml);
        } catch (Exception $e) {
            $error = false;
            Mage::log('Categories import failed: ' . $e->getMessage(), null, 'ceneoplpro.log');
        }
        return $error ? false : $this->importRes;
    }

    protected function parseAndSave($xml, $parent_id = 0, $path = '', $level = 0)
    {
        foreach ($xml->Category as $category) {
            $externalId = (int)$category->Id;
            $name = (string)$category->Name;
            $this->externalIds[] = $externalId;
            $_category = $this->loadByAttribute('external_id', $externalId);
            if (!$_category) {
                $this->setData(array(
                    'id' => null,
                    'parent_id' => $parent_id,
                    'external_id' => $externalId,
                    'level' => $level,
                    'name' => $name
                ))->save()->setPath($this->getNewPath($path, $this->getId()))->save();
                $this->importRes['created']++;
                Mage::log('Cateogory "' . $name . '" added', null, 'ceneoplpro.log');
            } else {
                $newPath = $this->getNewPath($path, $_category->getId());
                if ($_category->getName() != $name || $_category->getPath() != $newPath || $_category->getParentId() != $parent_id || $_category->getExternalId() != $externalId || $_category->getLevel() != $level) {
                    $_category->setName($name)
                        ->setPath($newPath)
                        ->setParentId($parent_id)
                        ->setExternalId($externalId)
                        ->setLevel($level)
                        ->setIsDeleted(0)
                        ->save();
                    Mage::log('Cateogory "' . $name . '" updated', null, 'ceneoplpro.log');
                    $this->importRes['updated']++;
                }
            }
            if (!empty($category->Subcategories)) {
                if ($_category) {
                    $this->parseAndSave($category->Subcategories, $_category->getId(), $_category->getPath(), $level + 1);
                } else {
                    $this->parseAndSave($category->Subcategories, $this->getId(), $this->getPath(), $level + 1);
                }
            }
            if ($_category) {
                $_category->clearInstance();
                unset($_category);
            }
            unset($category);
        }
        if ($level == 0 && !empty($this->externalIds)) {
            $coll = $this->getCollection()
                ->addFieldToFilter('external_id', array('nin' => $this->externalIds))
                ->addFieldToFilter('is_deleted', 0);
            foreach ($coll as $key => $item_to_delete) {
                $item_to_delete->setIsDeleted(1)
                    ->save();
                $this->importRes['deleted']++;
                $item_to_delete->clearInstance();
                unset($item_to_delete);
                $coll->removeItemByKey($key);
            }
        }
    }

    protected function getNewPath($start, $id)
    {
        return $start . (empty($start) ? '' : '/') . $id;
    }

    public function loadByAttribute($attribute, $value)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter($attribute, $value);
        $first = $collection->getFirstItem();
        if ($first->getId()) {
            return $first;
        }
        return false;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        if (sizeof($options) > 0) foreach ($options as $option) {
            if (isset($option['value']) && $option['value'] == $value) {
                return isset($option['label']) ? $option['label'] : $option['value'];
            }
        }
        if (isset($options[$value])) {
            return $options[$value];
        }
        return false;
    }

    public function getOptionId($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value) == 0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

}