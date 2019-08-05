<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $model = $this->getModel();
        // Mapping information
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('ceneoplpro')->__('Mapping Information'),
        ));
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
                'value' => $model->getId(),
            ));
        }
        $ceneo_categories = array('' => '') + Mage::getModel('ceneoplpro/category')->toOptionHash(false);
        $fieldset->addField('ceneopro_category_id', 'select', array(
            'name' => 'ceneopro_category_id',
            'label' => Mage::helper('ceneoplpro')->__('Ceneo Category'),
            'title' => Mage::helper('ceneoplpro')->__('Ceneo Category'),
            'options' => $ceneo_categories
        ));
        $fieldset->addField('priority', 'text', array(
            'name' => 'priority',
            'label' => Mage::helper('ceneoplpro')->__('Priority'),
            'title' => Mage::helper('ceneoplpro')->__('Priority'),
            'class' => 'validate-digits'
        ));


        // Rules
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/ceneoplpro_mapping/newConditionHtml', array('form' => 'conditions_fieldset')));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
                'legend'=>Mage::helper('catalogrule')->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);
        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('catalogrule')->__('Conditions'),
            'title' => Mage::helper('catalogrule')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('_current_mapping')->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        return parent::_prepareForm();

    }

    public function getModel() {
        return Mage::registry('_current_mapping');
    }
}
