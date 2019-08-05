<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 04.04.14
 */

class Orba_Ceneoplpro_Block_Cpacheckout extends Mage_Checkout_Block_Success {

    /**
     * @var Mage_Sales_Model_Order
     */
    private $_order = null;

    protected function _construct()
    {
        parent::_construct();
        $this->loadOrder();
    }

    /**
     * Loading current order for later use.
     */
    public function loadOrder()
    {
        if($this->_order === null) {
            $order_id = Mage::getSingleton('checkout/session')
                ->getLastRealOrderId();
            $this->_order = Mage::getModel('sales/order')
                ->loadByIncrementId($order_id);
        }
    }

    /**
     * Check whether CPA is all active in.
     *
     * @return bool
     */
    public function isCpaActive()
    {
        if((int) Mage::getStoreConfig('ceneoplpro/attr_cpa/cpa_active') == 1
            && (bool) $this->getCeneoGUID()) {
            return true;
        }
        return false;
    }

    /**
     * Returns CENEO client GUID
     *
     * @return String
     */
    public function getCeneoGUID()
    {
        return Mage::getStoreConfig('ceneoplpro/attr_cpa/cpa_guid');
    }

    /**
     * Returns client's email address
     *
     * @return string
     */
    public function getClientEmail()
    {
        return $this->_order->getCustomerEmail();
    }

    /**
     * Returns order real ID
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->_order->getRealOrderId();
    }

    /**
     * Returns order value with tax.
     *
     * @return string
     */
    public function getAmount()
    {
        return number_format($this->_order->getBaseSubtotalInclTax(), 2, '.', '');
    }

    /**
     * Returns formatted products ids.
     * Example: #234#112#112#5402
     *
     * @return string
     */
    public function getFormattedProductsIdsForCpa()
    {
        $items = $this->_order->getAllVisibleItems();
        $products_repeated = array_map(function($item) {
            return array_fill(0, (int) $item->getQtyToInvoice(), $item->getProduct()->getSku());
        }, $items);
        $_temp = array();
        foreach($products_repeated as $item) {
            $_temp = array_merge($_temp, $item);
        }
        return implode('#', $_temp);
    }

} 