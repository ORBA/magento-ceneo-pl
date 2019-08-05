<?php

class Orba_Ceneoplpro_Block_Config_Adminhtml_Form_Field_Attr extends Mage_Core_Block_Html_Select
{

    public function _toHtml()
    {
        $options = Mage::getSingleton('ceneoplpro/attribute')
            ->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }


    public function setInputName($value)
    {
        return $this->setName($value);
    }
}