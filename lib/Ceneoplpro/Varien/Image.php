<?php
/**
 * @copyright Copyright (c) 2016 Orba Sp. z o.o. (http://orba.co)
 */

class Ceneoplpro_Varien_Image extends Varien_Image
{
    /**
     * Loads new image using current adapter.
     * This prevents memory leaks when loading a lot of images in one run using standard Varien_Image.
     * @param string $fileName
     * @throws Exception
     */
    public function reload($fileName = null)
    {
        if (isset($this->_imageHandler)) {
            @imagedestroy($this->_imageHandler);
        }
        $this->_fileName = $fileName;
        if (isset($fileName) ) {
            $this->open();
        }
    }

    /**
     * Retrieve overwritten image adapter object.
     * @param string $adapter
     * @return Varien_Image_Adapter_Abstract
     */
    protected function _getAdapter($adapter=null)
    {
        if( !isset($this->_adapter) ) {
            $this->_adapter = new Ceneoplpro_Varien_Image_Adapter_Gd2();
        }
        return $this->_adapter;
    }
}
