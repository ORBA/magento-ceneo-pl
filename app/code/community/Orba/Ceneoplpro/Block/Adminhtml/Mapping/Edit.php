<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'ceneoplpro';
        $this->_controller = 'adminhtml_mapping';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('ceneoplpro')->__('Save Mapping')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('ceneoplpro')->__('Delete Mapping')
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }


    public function getHeaderText()
    {
        if (Mage::registry('_current_mapping') && Mage::registry('_current_mapping')->getId()) {
            return Mage::helper('ceneoplpro')->__("Edit Mapping");
        } else {
            return Mage::helper('ceneoplpro')->__('Add Mapping');
        }
    }

    public function getModel() {
        return Mage::registry('_current_mapping');
    }
}
