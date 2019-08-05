<?php
class Orba_Ceneoplpro_Adminhtml_Ceneoplpro_MappingController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed() {
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('catalog/ceneoplpro/mapping_index');
    }

	public function indexAction() {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Ceneo.pl'))
            ->_title($this->__('Mass Categories Mapping'));
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function newAction() {
        $this->_forward('edit');
    }
    
    public function editAction () {
        $model = Mage::getModel('ceneoplpro/mapping');
        if ($id = $this->getRequest()->getParam('id')) {
            $model->load($id);
        }
        Mage::register('_current_mapping', $model);
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)->setCanLoadRulesJs(true);
        $this->_setActiveMenu('ceneoplpro/mapping');
        if ($model->getId()) {
            $breadcrumb_title = Mage::helper('ceneoplpro')->__('Edit Mapping');
            $breadcrumb_label = $breadcrumb_title;
        }
        else {
            $breadcrumb_title = Mage::helper('ceneoplpro')->__('New Mapping');
            $breadcrumb_label = Mage::helper('ceneoplpro')->__('Create Mapping');
        }
        $this->_title($breadcrumb_title);
        $this->_addBreadcrumb($breadcrumb_label, $breadcrumb_title);
        // restore data
        if ($values = $this->_getSession()->getData('mapping_form_data', true)) {
            $model->addData($values);
        }
        if ($edit_block = $this->getLayout()->getBlock('mapping_edit')) {
            $edit_block->setEditMode($model->getId() > 0);
        }
        $model->getConditions()->setJsFormObject('conditions_fieldset');
        $this->renderLayout();
    }
    
    public function saveAction () {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/mapping'));
        }
        $mapping = Mage::getModel('ceneoplpro/mapping');
        if ($id = (int)$request->getParam('id')) {
            $mapping->load($id);
        }
        $redirected = false;
        try {
            $data = $this->getRequest()->getPost();
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
            $mapping->addData($data);
            $mapping->loadPost($data);
            $mapping->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ceneoplpro')->__('The mapping has been saved.'));
            $this->_redirect('*/*');
            $redirected = true;
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('ceneoplpro')->__('An error occurred while saving this mapping.'));
            $this->_getSession()->setData('mapping_form_data', $this->getRequest()->getParams());
        }
        if (!$redirected) {
            $this->_forward('new');
        }
    }

    public function deleteAction() {
        $mapping = Mage::getModel('ceneoplpro/mapping')
            ->load($this->getRequest()->getParam('id'));
        if ($mapping->getId()) {
            $success = false;
            try {
                $mapping->delete();
                $success = true;
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('ceneoplpro')->__('An error occurred while deleting this mapping.'));
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('ceneoplpro')->__('The mapping has been deleted.'));
            }
        }
        $this->_redirect('*/*');
    }


    public function massDeleteAction()
    {
        $ceneoEntityIds = $this->getRequest()->getParam('ceneo_massaction');
        if (!is_array($ceneoEntityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ceneoplpro')->__('Please select mapping to delete.')
            );
        } else {
            try {
                foreach ($ceneoEntityIds as $ceneoEntityId) {
                    $ceneoMappingModel = Mage::getModel('ceneoplpro/mapping');
                    $ceneoMappingModel->setId($ceneoEntityId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ceneoplpro')->__('Total of %d mapping were successfully deleted.', count($ceneoEntityIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ceneoplpro')->__('There was an error deleting mapping.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function runAction() {
        $mapping = Mage::getModel('ceneoplpro/mapping')
            ->load($this->getRequest()->getParam('id'));
        if ($mapping->run()) {
            $this->_getSession()->addSuccess(Mage::helper('ceneoplpro')->__('The mapping has been finished successfully.'));
        } else {
            $this->_getSession()->addError(Mage::helper('ceneoplpro')->__('An error occurred while running this mapping.'));
        }
        $this->_redirect('*/*');
    }
    
    public function runallAction() {
        if (Mage::getModel('ceneoplpro/run')->runAll()) {
            $this->_getSession()->addSuccess(Mage::helper('ceneoplpro')->__('The mapping has been finished successfully.'));
        } else {
            $this->_getSession()->addError(Mage::helper('ceneoplpro')->__('An error occurred while running mappings.'));
        }
        $this->_redirect('*/*');
    }
    
    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('catalogrule/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * grid action
     *
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }
    
}