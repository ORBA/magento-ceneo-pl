<?php

class Orba_Ceneoplpro_Block_Admin_Offer_Grid_Renderer_Warning extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        $warning = '';
        if ($row->getData('ceneopro_category_id')) {
            $store = Mage::registry('ceneoplpro_offer_grid_current_store');
            $product = Mage::getModel('ceneoplpro/product')
                    ->setStoreId($store->getId())
                    ->load($row->getData('entity_id'));
            $price = $product->getFinalPriceIncludingTax($product);
            if (!$price) {
                $warning = $this->__('Price is set to 0!');
            }
        }
        return $warning ? '<span style="color: red;">'.$warning.'</span>' : '';
    }
    
}