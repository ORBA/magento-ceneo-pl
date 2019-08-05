<?php
class Orba_Ceneoplpro_Adminhtml_Ceneoplpro_CategoryController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed() {
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('catalog/ceneoplpro/category_' . $this->getRequest()->getActionName());
    }
    
    public function refreshAction() {
        $res = Mage::getModel('ceneoplpro/category')->doImport();
        if ($res) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ceneoplpro')->__('Categories import is finished. Created: %s. Updated: %s. Deleted: %s.', $res['created'], $res['updated'], $res['deleted']));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ceneoplpro')->__('Unable to import categories.'));
        }
        $this->_redirectReferer();
    }
    
}