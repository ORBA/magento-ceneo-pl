<?php
class Orba_Ceneoplpro_Model_Mapping_Condition_Combine extends Mage_Rule_Model_Condition_Combine {
    
    public function __construct() {
        parent::__construct();
        $this->setType('ceneoplpro/mapping_condition_combine');
    }

    public function getNewChildSelectOptions() {
        $productCondition = Mage::getModel('ceneoplpro/mapping_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code=>$label) {
            $attributes[] = array('value'=>'ceneoplpro/mapping_condition_product|'.$code, 'label'=>$label);
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'ceneoplpro/mapping_condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalogrule')->__('Product Attribute'), 'value'=>$attributes),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
