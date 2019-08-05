<?php
/**
 * @copyright Copyright (c) 2016 Orba Sp. z o.o. (http://orba.co)
 */

class Ceneoplpro_Varien_Image_Adapter_Gd2 extends Varien_Image_Adapter_Gd2
{
    /**
     * Overwritten abstract method.
     * We're not saving mime type to class field so it would be calculated every time.
     * @return string
     */
    public function getMimeType()
    {
        list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType, ) = getimagesize($this->_fileName);
        $this->_fileMimeType = image_type_to_mime_type($this->_fileType);
        return $this->_fileMimeType;
    }
}