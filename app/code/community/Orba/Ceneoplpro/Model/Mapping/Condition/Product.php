<?php
class Orba_Ceneoplpro_Model_Mapping_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {
    
    public function loadAttributeOptions() {
        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();
        $attributes = array();
        foreach ($productAttributes as $key => $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }
        if (!isset($attributes['category_ids'])) {
            $attributes['category_ids'] = 'category_ids';
        }
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
    
    public function getAttributeElement() {
        if (is_null($this->getAttribute())) {
            foreach ($this->getAttributeOption() as $k => $v) {
                $this->setAttribute($k);
                break;
            }
        }
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__attribute', 'select', array(
            'name'=>'rule['.$this->getPrefix().']['.$this->getId().'][attribute]',
            'values'=>$this->getAttributeSelectOptions(),
            'value'=>$this->getAttribute(),
            'value_name'=>$this->getAttributeName(),
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }
    
}
