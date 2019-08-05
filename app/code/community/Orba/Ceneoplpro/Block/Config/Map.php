<?php

class Orba_Ceneoplpro_Block_Config_Map extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_itemRenderer;

    public function _prepareToRender()
    {
        $this->addColumn('ceneo_attr_name', array(
            'label' => Mage::helper('ceneoplpro')->__('Attribute in Ceneo Feed'),
            'style' => 'width:100px',
        ));
        $this->addColumn('magento_attr_id', array(
            'label' => Mage::helper('ceneoplpro')->__('Magento Attribute'),
            'renderer' => $this->_getRenderer(),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('ceneoplpro')->__('Add');
    }

    protected function _getRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'ceneoplpro/config_adminhtml_form_field_attr', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()
                ->calcOptionHash($row->getData('magento_attr_id')),
            'selected="selected"'
        );
    }
}