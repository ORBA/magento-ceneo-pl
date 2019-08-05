<?php
class Orba_Ceneoplpro_ProductsController extends Mage_Core_Controller_Front_Action {
	
    const FEED_CACHE_ID_PREFIX = 'ceneopro_feed_';
    
    protected function getConfig() {
        return Mage::getSingleton('ceneoplpro/config');
    }
    
    public function feedAction() {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash == $this->getConfig()->getHash()) {
            $this->getResponse()
                    ->setHeader('Content-Type', 'text/xml')
                    ->setBody($this->_getBody());
        } else {
            $this->_redirect('/');
        }
    }
    
    protected function _getBody() {
        if ($this->getConfig()->isFeedLive() || $this->getRequest()->getParam('live') === '1') {
            return $this->_getBodyLive();
        } else {
            return $this->_getBodyCached();
        }
    }
    
    protected function _getBodyLive() {
        ini_set('max_execution_time', 0);
        require_once(Mage::getBaseDir('lib').'/Ceneoplpro/simple_xml_extended.php');
        $offers = Mage::getModel('ceneoplpro/product')->getOffers();
        $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1" />');
        foreach ($offers as $group_name => $products) {
            foreach ($products as $product) {

                $o = $xml->addChild('o');
                $o->addAttribute('id', $product['id']);
                $o->addAttribute('url', $product['url']);
                $o->addAttribute('price', $product['price']);
                if (!empty($product['weight'])) {
                    $o->addAttribute('weight', $product['weight']);
                }
                foreach ($product['core_attrs'] as $attr => $value) {
                    $o->addAttribute($attr, $value);
                }
                if ($product['cat']) {
                    $o->addChild('cat')
                        ->addCData(implode('/', $product['cat']));
                }
                $o->addChild('name')
                    ->addCData($product['name']);
                $o->addChild('desc')
                    ->addCData($product['desc']);
                if (!empty($product['imgs'])) {
                    $imgs = $o->addChild('imgs');
                    $imgs->addChild('main')
                        ->addAttribute('url', $product['imgs'][0]);
                    if (isset($product['imgs'][1])) {
                        $imgs->addChild('i')
                            ->addAttribute('url', $product['imgs'][1]);
                    }
                }
                $attrs = $o->addChild('attrs');
                foreach ($product['group_attrs'] as $attr => $value) {
                    $a = $attrs->addChild('a');
                    $a->addAttribute('name', $attr);
                    $a->addCData($value);
                }
                $a= $attrs->addChild('a');
                $a->addAttribute('name', 'namePl');

                /* Checkin for a polish name. If empty duplicate main name */
                if (!empty($product['namePl'])) {	                    
                    $a->addCData($product['namePl']);
                } else {
                    $a->addCData($product['name']);	                    
                } 
                unset($product);
            }
        }
        unset($offers);
        $body = $xml->asXML();
        $this->_cacheBody($body);
        return $body;
    }
    
    protected function _getCacheId() {
        return self::FEED_CACHE_ID_PREFIX . $this->getConfig()->getStore()->getId();
    }
    
    protected function _getBodyCached() {
        if (false !== ($data = Mage::app()->getCache()->load($this->_getCacheId()))) {
            return $data;
        } else {
            return $this->_getBodyLive();
        }
    }
    
    protected function _cacheBody($body) {
        Mage::app()->getCache()->save($body, $this->_getCacheId(), array(Orba_Ceneoplpro_Model_Config::CACHE_TAG), 3600 * 12);
    }

}