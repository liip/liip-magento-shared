<?php

/**
 * Proper umlaut url replacements
 */
class Liip_Shared_Helper_Catalog_Product_Url extends Mage_Catalog_Helper_Product_Url
{
    public function __construct()
    {
        parent::__construct();
        $this->_convertTable['Ä'] = 'ae';
        $this->_convertTable['ä'] = 'ae';
        $this->_convertTable['Ö'] = 'oe';
        $this->_convertTable['ö'] = 'oe';
        $this->_convertTable['Ü'] = 'ue';
        $this->_convertTable['ü'] = 'ue';
    }
}

