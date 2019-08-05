<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'ceneoentitycode_stores_form',
            array('legend' => Mage::helper('ceneoplpro')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('ceneoplpro')->__('Store Views'),
                'title'    => Mage::helper('ceneoplpro')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('_current_mapping')->getData());
        return parent::_prepareForm();
    }
}
