<?php
class Orba_Ceneoplpro_Model_Catalog_Category extends Mage_Catalog_Model_Category {
    
    public function getAllOptions() {
        $options = $this->getFlatTree();
        return $options;
    }
    
    public function getFlatTree($parent = null) {
        $res = array();
        $parent_id = ($parent === null) ? 0 : $parent->getId();
        $category_collection = $this->getCollection()
            ->addFieldToFilter('parent_id', $parent_id)
            ->addAttributeToSelect('name')
            ->setOrder('position', 'asc');
        foreach ($category_collection as $key => $category) {
            if ($parent === null) {
                $category->setNamePath($category->getName());
            } else {
                $category->setNamePath($parent->getNamePath().' / '.$category->getName());
            }
            $res[] = array(
                'label' => $category->getNamePath(),
                'value' => $category->getId()
            );
            $res = array_merge($res, $this->getFlatTree($category));
            $category->clearInstance();
            unset($category);
            $category_collection->removeItemByKey($key);
        }
        unset($category_collection);
        return $res;
    }
    
    public function toOptionHash() {
        $options = $this->getAllOptions();
        $option_hash = array();
        foreach ($options as $option) {
            $option_hash[$option['value']] = $option['label'];
        }
        return $option_hash;
    }
    
    public function getChildrenIds($id, $tree = null) {
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
    
}