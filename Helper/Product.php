<?php

class Liip_Shared_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Load a product by SKU.
     * 
     * @param string $sku SKU
     * @return Mage_Catalog_Model_Product the product model
     */
    public function load($sku)
    {
        $product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySku($sku));
        return $product;
    }
}
