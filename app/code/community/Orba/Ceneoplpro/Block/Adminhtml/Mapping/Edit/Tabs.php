<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ceneo_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ceneoplpro')->__('Mapping Information'));
    }


    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_ceneo_tab',
            array(
                'label'   => Mage::helper('ceneoplpro')->__('General'),
                'title'   => Mage::helper('ceneoplpro')->__('General'),
                'content' => $this->getLayout()->createBlock(
                    'ceneoplpro/adminhtml_mapping_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_ceneo_tab',
                array(
                    'label'   => Mage::helper('ceneoplpro')->__('Store views'),
                    'title'   => Mage::helper('ceneoplpro')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'ceneoplpro/adminhtml_mapping_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }


    public function getCeneoCurrentMapping()
    {
        return Mage::registry('_current_mapping');
    }
}
