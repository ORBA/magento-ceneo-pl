<?php
class Orba_Ceneoplpro_Block_Admin_Mapping_Edit extends Mage_Adminhtml_Block_Widget {

    protected $_editMode = false;

    public function getModel() {
        return Mage::registry('_current_mapping');
    }

    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ceneoplpro')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ceneoplpro')->__('Reset'),
                    'onclick' => 'window.location.href = window.location.href'
                ))
        );
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ceneoplpro')->__('Save Mapping'),
                    'onclick' => 'mappingControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('newsletter')->__('Delete Mapping'),
                    'onclick' => 'mappingControl.deleteMapping();',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }

    public function getResetButtonHtml() {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml() {
        return $this->getChildHtml('save_button');
    }

    public function getDeleteButtonHtml() {
        return $this->getChildHtml('delete_button');
    }

    public function getSaveAsButtonHtml() {
        return $this->getChildHtml('save_as_button');
    }

    public function setEditMode($value = true) {
        $this->_editMode = (bool)$value;
        return $this;
    }

    public function getEditMode() {
        return $this->_editMode;
    }

    public function getHeaderText() {
        if ($this->getEditMode()) {
            return Mage::helper('ceneoplpro')->__('Edit Mapping');
        }
        return  Mage::helper('ceneoplpro')->__('New Mapping');
    }

    public function getForm() {
        return $this->getLayout()
            ->createBlock('ceneoplpro/admin_mapping_edit_form')
            ->toHtml();
    }

    /**
     * Return return template name for JS
     *
     * @return string
     */
    public function getJsTemplateName()
    {
        return addcslashes($this->getModel()->getTemplateCode(), "\"\r\n\\");
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save');
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
    }

    public function getSaveAsFlag() {
        return $this->getRequest()->getParam('_save_as_flag') ? '1' : '';
    }

    protected function isSingleStoreMode() {
        return Mage::app()->isSingleStoreMode();
    }

    protected function getStoreId() {
        return Mage::app()->getStore(true)->getId();
    }
}
