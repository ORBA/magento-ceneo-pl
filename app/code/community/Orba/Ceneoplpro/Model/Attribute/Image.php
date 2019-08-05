<?php
class Orba_Ceneoplpro_Model_Attribute_Image extends Mage_Core_Model_Abstract {
    
    /**
     * Option values
     */
    const IMAGE_IMAGE   = 'image';
    const IMAGE_SMALL_IMAGE   = 'small_image';
    const IMAGE_THUMBNAIL = 'thumbnail';

    /**
     * Retrieve all options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => 'Base image',
                    'value' => self::IMAGE_IMAGE
                ),
                array(
                    'label' => 'Small image',
                    'value' => self::IMAGE_SMALL_IMAGE
                ),
                array(
                    'label' => 'Thumbnail',
                    'value' => self::IMAGE_THUMBNAIL
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve option array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    
    /**
     * Gets option label by code.
     * Returns false if code doesn't exist.
     *
     * @param string $code
     * @return string|false
     */
    public function getOptionsByCode($code) {
        $options = $this->getOptionArray();
        if (isset($options[$code])) {
            return $options[$code];
        }
        return false;
    }    
}