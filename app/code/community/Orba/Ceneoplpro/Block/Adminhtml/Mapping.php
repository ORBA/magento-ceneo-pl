<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller         = 'adminhtml_mapping';
        $this->_blockGroup         = 'ceneoplpro';
        parent::__construct();
        $this->_headerText         = Mage::helper('ceneoplpro')->__('Mass Categories Mapping');
        $this->_updateButton('add', 'label', Mage::helper('ceneoplpro')->__('Add Mapping'));
        $this->_addButton('run_all', array(
            'label'   => $this->__('Run All'),
            'onclick' => "setLocation('{$this->getUrl('*/*/runall')}')"
        ));

    }
}
