<?php
class Orba_Ceneoplpro_Block_Admin_Offer extends Mage_Adminhtml_Block_Widget_Container {
    
    public function __construct() {
        parent::__construct();
        $this->setTemplate('ceneoplpro/offer.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock('ceneoplpro/admin_offer_grid', 'ceneoplpro_offer_grid'));
        return parent::_prepareLayout();
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode() {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }
    
}
