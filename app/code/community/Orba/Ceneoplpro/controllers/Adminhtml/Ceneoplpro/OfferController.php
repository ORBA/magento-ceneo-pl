<?php
class Orba_Ceneoplpro_Adminhtml_Ceneoplpro_OfferController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed() {
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('catalog/ceneoplpro/offer_' . $this->getRequest()->getActionName());
    }

	public function indexAction() {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Ceneo.pl'))
            ->_title($this->__('Offer'));
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function urlsAction() {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Ceneo.pl'))
            ->_title($this->__('Feed URLs'));
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function regenerateAction() {
        if (Mage::getSingleton('ceneoplpro/product')->regenerateFeeds()) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ceneoplpro')->__('XML feeds have been regenerated.'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ceneoplpro')->__('Unable to regenerate XML feeds.'));
        }
        $this->_redirectReferer();
    }
    
}